<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */


$items = [];


if (Yii::$app->user->can('app_item_view', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-eye"></i> ' . Yii::t('app', 'View'),
        'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'view',
        'encode' => false,
    ];
}

if (Yii::$app->user->can('app_item_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update'
    ];
}

if (Yii::$app->user->can('app_item_split', ['route' => true])) {
    if ($model->split_id) {
        $items[] = [
            'label' => '<i class="fa icon-merge"></i> ' . Yii::t('app', 'Merge'),
            'url' => ['split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
            'linkOptions' => [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ],
            'encode' => false,
            'active' => Yii::$app->controller->action->id == 'update',
        ];
    } elseif ($model->quantity * $model->product->quantity > 1) {
        $items[] = [
            'label' => '<i class="fa icon-split"></i> ' . Yii::t('app', 'Split'),
            'url' => ['split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
            'linkOptions' => [
                'class' => 'modal-remote',
            ],
            'encode' => false,
            'active' => Yii::$app->controller->action->id == 'update',
        ];
    }
}

if (Yii::$app->user->can('app_item_print', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-print"></i> ' . Yii::t('app', 'Print'),
        'url' => ['print', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'linkOptions' => [
            'class' => 'modal-remote',
        ],
        'encode' => false,
    ];
}

$auditItems = [];
if (Yii::$app->user->can('app_item_log', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Log'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'log',
    ];
}
if (Yii::$app->user->can('app_item_trail', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Trail'),
        'url' => ['trail', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'trail',
    ];
}
if ($auditItems) {
    $items[] = [
        'label' => '<i class="fa fa-database"></i> ' . Yii::t('app', 'Audit'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'items' => $auditItems,
    ];
}

if (Yii::$app->user->can('app_item_delete', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'),
        'url' => ['delete', 'id' => $model->id],
        'linkOptions' => [
            'data-confirm' => Yii::t('app', 'Are you sure?'),
            'data-method' => 'post',
        ],
        'encode' => false,
    ];
}

$this->params['nav'] = $items;

//echo Nav::widget([
//    'items' => $items,
//    'options' => ['class' => 'nav-tabs'],
//    'activateParents' => true,
//]);
