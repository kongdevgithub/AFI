<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

class Tree extends \dmstr\modules\pages\models\Tree
{
    /**
     * @param string $domainId the domain id of the root node
     * @return Tree[] active and visible menu nodes for the current application language
     */
    public static function getMenuItems($domainId, $checkUserPermissions = false)
    {
        $user = Yii::$app->user;

        // Get root node by domain id
        $rootCondition['domain_id'] = $domainId;
        $rootCondition['access_domain'] = [self::GLOBAL_ACCESS_DOMAIN, mb_strtolower(\Yii::$app->language)];
        if (!$user->can('pages')) {
            $rootCondition[self::ATTR_DISABLED] = self::NOT_DISABLED;
        }
        $rootNode = self::findOne($rootCondition);

        if ($rootNode === null) {
            return [];
        }

        /*
         * @var $leaves Tree[]
         */

        // Get all leaves from this root node
        $leavesQuery = $rootNode->children()->andWhere(
            [
                self::ATTR_ACTIVE => self::ACTIVE,
                //self::ATTR_VISIBLE => self::VISIBLE,
                self::ATTR_ACCESS_DOMAIN => [self::GLOBAL_ACCESS_DOMAIN, mb_strtolower(\Yii::$app->language)],
            ]
        );
        if (!$user->can('pages')) {
            $leavesQuery->andWhere(
                [
                    self::ATTR_DISABLED => self::NOT_DISABLED,
                ]
            );
        }

        /** @var static[] $leaves */
        $leaves = $leavesQuery->all();

        if ($leaves === null) {
            return [];
        }

        // tree mapping and leave stack
        $treeMap = [];
        $stack = [];

        if (count($leaves) > 0) {
            foreach ($leaves as $page) {

                // prepare node identifiers
                $pageOptions = [
                    'data-page-id' => $page->id,
                    'data-lvl' => $page->lvl,
                ];

                $route = substr(str_replace('/', '_', $page->route), 1);
                if ($route) {
                    $route = 'app_' . $route;
                } else {
                    $route = 'app';
                }

                $visible = $page->visible;
                if ($visible && $checkUserPermissions) {
                    if (!$user->can($route, ['route' => true]) && !$user->can($route . '_index', ['route' => true])) {
                        $visible = false;
                    }
                }

                $itemTemplate = [
                    'label' => $page->name,
                    'url' => $page->createRoute(),
                    'icon' => $page->icon,
                    'linkOptions' => $pageOptions,
                    'visible' => $visible,
                ];
                $item = $itemTemplate;

                // Count items in stack
                $counter = count($stack);

                // Check on different levels
                while ($counter > 0 && $stack[$counter - 1]['linkOptions']['data-lvl'] >= $item['linkOptions']['data-lvl']) {
                    array_pop($stack);
                    --$counter;
                }

                // Stack is now empty (check root again)
                if ($counter == 0) {
                    // assign root node
                    $i = count($treeMap);
                    $treeMap[$i] = $item;
                    $stack[] = &$treeMap[$i];
                } else {
                    if (!isset($stack[$counter - 1]['items'])) {
                        $stack[$counter - 1]['items'] = [];
                    }
                    // add the node to parent node
                    $i = count($stack[$counter - 1]['items']);
                    $stack[$counter - 1]['items'][$i] = $item;
                    $stack[] = &$stack[$counter - 1]['items'][$i];
                }
            }
        }

        return array_filter($treeMap);
    }

    public function createRoute($additionalParams = [])
    {
        $route = [
            '/' . $this->route,
            //'pageId' => $this->id,
            //'pageSlug' => ($this->page_title)
            //    ? Inflector::slug($this->page_title)
            //    : Inflector::slug($this->name),
            //'parentLeave' => ($this->parents(1)->one() && !$this->parents(1)->one()->isRoot())
            //    ? Inflector::slug($this->parents(1)->one()->name)
            //    : null,
        ];

        if (Json::decode($this->request_params)) {
            $route = ArrayHelper::merge($route, Json::decode($this->request_params));
        }

        if (!empty($additionalParams)) {
            $route = ArrayHelper::merge($route, $additionalParams);
        }

        return $route;
    }

}