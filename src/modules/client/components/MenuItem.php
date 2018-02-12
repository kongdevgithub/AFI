<?php

namespace app\modules\client\components;

use app\components\DynamicMenu;
use Yii;
use yii\helpers\Html;


/**
 * MenuItem
 * @package app\components
 */
class MenuItem
{

    /**
     * @param string $until
     * @return string
     */
    public static function getNewLabel($until = 'tomorrow')
    {
        if (time() < strtotime($until)) {
            return ' <span class="label label-info">new</span>';
        }
        return '';
    }

    /**
     * @param bool $includeHistory
     * @return array
     */
    public static function getNavItems($includeHistory = true)
    {
        $items = [];
        $subItems = static::getJobsItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('app', 'Jobs') . '</span>',
                //'url' => ['//job'],
                'icon' => 'icon fa fa-folder',
                'items' => $subItems,
            ];
        }
        if ($includeHistory) {
            $subItems = DynamicMenu::getMenuItems();
            if ($subItems) {
                $items[] = [
                    'icon' => 'icon fa fa-history',
                    'items' => $subItems,
                ];
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public static function getJobsItems()
    {
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Create Job'),
            'url' => ['//client/job/create'],
            'icon' => 'icon fa fa-plus',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Jobs'),
            'url' => ['//client/job'],
            'icon' => 'icon fa fa-folder',
            'items' => [
                [
                    'label' => Yii::t('app', 'Product Create'),
                    'url' => ['//product/create'],
                    'visible' => 0,
                ],
            ],
        ];
        //$items[] = [
        //    'label' => Yii::t('app', 'Products'),
        //    'url' => ['//client/product'],
        //    'icon' => 'icon fa fa-object-group',
        //];
        //$items[] = [
        //    'label' => Yii::t('app', 'Items'),
        //    'url' => ['//client/item'],
        //    'icon' => 'icon fa fa-tag',
        //];
        //$items[] = [
        //    'label' => Yii::t('app', 'Packages'),
        //    'url' => ['//client/package'],
        //    'icon' => 'icon fa fa-archive',
        //];
        //$items[] = [
        //    'label' => Yii::t('app', 'Pickups'),
        //    'url' => ['//client/pickup'],
        //    'icon' => 'icon fa fa-truck',
        //];
        return $items;
    }

    /**
     * @return array
     */
    public static function getCustomItems()
    {
        $items = [];
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
        return $items;
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
            'label' => Html::tag('div', Html::a(Yii::t('app', 'Profile'), ['//user/settings/profile'], ['class' => 'btn btn-default btn-flat']), ['class' => 'pull-left'])
                . Html::tag('div', Html::a(Yii::t('app', 'Sign out'), ['//user/security/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']), ['class' => 'pull-right']),
            'options' => ['class' => 'user-footer'],
        ];
        return $items;
    }
}