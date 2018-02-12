<?php

namespace app\modules\goldoc\components;

use app\components\DynamicMenu;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;


/**
 * MenuItem
 * @package app\components
 */
class MenuItem
{

    /**
     * @return array
     */
    public static function getNavItems()
    {
        $items = [];
        $subItems = static::getDashboardsItems();
        if ($subItems) {
            $items[] = [
                //'label' => Yii::t('goldoc', 'Dashboards'),
                'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Dashboards') . '</span>',
                //'url' => ['//dashboard'],
                'icon' => 'icon fa fa-tachometer',
                'items' => $subItems,
            ];
        }
        $subItems = static::getReportsItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Reports') . '</span>',
                //'url' => ['//report'],
                'icon' => 'icon fa fa-area-chart',
                'items' => $subItems,
            ];
        }
        $subItems = static::getProductItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Products') . '</span>',
                //'url' => ['//report'],
                'icon' => 'icon fa fa-shopping-cart',
                'items' => $subItems,
            ];
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getCustomItems()
    {
        $items = [];
        if (Yii::$app->user->can('staff')) {
            $items[] = [
                //'label' => Yii::t('goldoc', 'AFI Console'),
                'url' => Url::home(),
                'icon' => 'icon fa fa-home',
            ];
        }
        $subItems = static::getSettingsItems();
        if ($subItems) {
            $items[] = [
                //'label' => Yii::t('goldoc', 'Settings'),
                //'url' => ['//component'],
                'icon' => 'icon fa fa-wrench',
                'items' => $subItems,
            ];
        }
        /** @var \app\models\User $identity */
        $subItems = static::getUserItems();
        if ($subItems) {
            $identity = Yii::$app->user->identity;
            $items[] = [
                'label' => $identity->getAvatar(25, ['class' => 'user-image']) . ' ' . $identity->username,
                'encode' => false,
                'items' => $subItems,
                'options' => ['class' => 'user user-menu'],
            ];
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getDashboardsItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['dashboard']) ? $_GET['dashboard'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('goldoc', 'GOLDOC'),
            'url' => ['//goldoc/dashboard/goldoc'],
            'active' => ($c == 'dashboard' && $a == 'goldoc')
        ];
        $activeItems = [];
        $activeItems[] = [
            'label' => Yii::t('goldoc', 'All'),
            'url' => ['//goldoc/dashboard/active'],
        ];
        $activeItems[] = [
            'label' => Yii::t('goldoc', 'None'),
            'url' => ['//goldoc/dashboard/active', 'ProductSearch' => ['supplier_id' => 0]],
        ];
        $activeItems[] = [
            'label' => Yii::t('goldoc', 'AFI'),
            'url' => ['//goldoc/dashboard/active', 'ProductSearch' => ['supplier_id' => 1]],
        ];
        $activeItems[] = [
            'label' => Yii::t('goldoc', 'ADG'),
            'url' => ['//goldoc/dashboard/active', 'ProductSearch' => ['supplier_id' => 2]],
        ];
        $items[] = [
            'label' => Yii::t('goldoc', 'ACTIVE'),
            'url' => ['//goldoc/dashboard/active'],
            'active' => ($c == 'dashboard' && $a == 'active'),
            'items' => $activeItems,
        ];
        $items[] = [
            'label' => Yii::t('goldoc', 'Staff Contacts'),
            'url' => ['//goldoc/default/staff'],
            'active' => ($c == 'default' && $a == 'staff'),
        ];
        $items[] = [
            'label' => Yii::t('goldoc', 'Glossary'),
            'url' => ['//goldoc/default/glossary'],
            'active' => ($c == 'default' && $a == 'glossary'),
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getSettingsItems()
    {
        $items = [];
        $items [] = [
            'label' => Yii::t('goldoc', 'Colours'),
            'url' => ['//goldoc/colour'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Designs'),
            'url' => ['//goldoc/design'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Installers'),
            'url' => ['//goldoc/installer'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Items'),
            'url' => ['//goldoc/item'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Sponsors'),
            'url' => ['//goldoc/sponsor'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Substrates'),
            'url' => ['//goldoc/substrate'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Suppliers'),
            'url' => ['//goldoc/supplier'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Types'),
            'url' => ['//goldoc/type'],
        ];
        $items [] = [
            'label' => Yii::t('goldoc', 'Venues'),
            'url' => ['//goldoc/venue'],
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getUserItems()
    {
        $user = Yii::$app->user;
        /** @var \app\models\User $identity */
        $identity = $user->identity;
        $items = [];
        $items[] = [
            'label' => $identity->getAvatar(90, ['class' => 'img-circle']) . '<p>' . $identity->label . ' [' . $identity->username . ']<small>' . $identity->email . '</small></p>',
            'options' => ['class' => 'user-header'],
        ];
        $items[] = [
            'label' => Html::tag('div', Html::a(Yii::t('goldoc', 'Profile'), ['//user/settings/profile'], ['class' => 'btn btn-default btn-flat']), ['class' => 'pull-left'])
                . Html::tag('div', Html::a(Yii::t('goldoc', 'Sign out'), ['//user/security/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']), ['class' => 'pull-right']),
            'options' => ['class' => 'user-footer'],
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getProductItems()
    {
        $items[] = [
            'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Products') . '</span>',
            //'url' => ['//report'],
            'icon' => 'icon fa fa-picture-o',
            'url' => ['//goldoc/product'],
        ];
        $items [] = [
            'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Signage FAs') . '</span>',
            'url' => ['//goldoc/signage-fa'],
            'icon' => 'icon fa fa-file-text-o',
        ];
        $items [] = [
            'label' => '<span class="hidden-sm">' . Yii::t('goldoc', 'Signage Wayfindings') . '</span>',
            'url' => ['//goldoc/signage-fa'],
            'icon' => 'icon fa fa-map-signs',
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getReportsItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('goldoc', 'Budget Overview'),
            'url' => ['//goldoc/report/budget-overview'],
            'active' => ($c == 'report' && $a == 'budget-overview')
        ];
        $items[] = [
            'label' => Yii::t('goldoc', 'Budget Sponsors'),
            'url' => ['//goldoc/report/budget-sponsors'],
            'active' => ($c == 'report' && $a == 'budget-sponsors')
        ];
        $items[] = [
            'label' => Yii::t('goldoc', 'Production'),
            'url' => ['//goldoc/report/production'],
            'active' => ($c == 'report' && $a == 'production')
        ];
        return static::cleanItems($items);
    }

    /**
     * @param $items
     * @return mixed
     */
    protected static function cleanItems($items)
    {
        foreach ($items as $k => $item) {
            $items[$k] = static::cleanItem($item);
            if (!$items[$k]) {
                unset($items[$k]);
            }
        }
        return $items;
    }

    /**
     * @param $item
     * @return mixed
     */
    public static function cleanItem($item)
    {
        if (isset($item['url']) && is_array($item['url'])) {
            $route = str_replace('/', '_', static::normalizeRoute($item['url'][0]));
            $route = $route ? $route : 'goldoc_default';
            $visible = isset($item['visible']) ? $item['visible'] : true;
            if ($visible) {
                $user = Yii::$app->user;
                if (!$user->can($route, ['route' => true]) && !$user->can($route . '_index', ['route' => true])) {
                    //$visible = false;
                    return false;
                }
            } else {
                return false;
            }
            //$item['visible'] = $visible;
        }
        return $item;
    }

    /**
     * @param $route
     * @return string
     */
    protected static function normalizeRoute($route)
    {
        $route = Yii::getAlias((string)$route);
        if (strncmp($route, '/', 1) === 0) {
            return ltrim($route, '/');
        }
        if (Yii::$app->controller === null) {
            return '';
        }
        if (strpos($route, '/') === false) {
            return $route === '' ? Yii::$app->controller->getRoute() : Yii::$app->controller->getUniqueId() . '/' . $route;
        }
        return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
    }

}