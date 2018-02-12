<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */

$items = [];

$items[] = [
    'label' => '<i class="fa fa-eye"></i> ' . Yii::t('app', 'View'),
    'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
    'encode' => false,
];
if (Yii::$app->user->can('app_package_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update',
    ];
}

$printItems = [];
if (Yii::$app->user->can('app_package_print', ['route' => true])) {
    $printItems[] = [
        'label' => Yii::t('app', 'Print'),
        'url' => ['print', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'linkOptions' => ['class' => 'modal-remote'],
    ];
}
if (Yii::$app->user->can('app_package_pdf', ['route' => true])) {
    $printItems[] = [
        'label' => Yii::t('app', 'PDF'),
        'url' => ['pdf', 'id' => $model->id, 'time' => time()],
        'linkOptions' => ['target' => '_blank'],
    ];
}
if ($printItems) {
    $items[] = [
        'label' => '<i class="fa fa-print"></i> ' . Yii::t('app', 'Print'),
        'url' => ['production', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'print',
        'items' => $printItems,
        'encode' => false,
    ];
}

$auditItems = [];
if (Yii::$app->user->can('app_package_log', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Log'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'log',
    ];
}
if (Yii::$app->user->can('app_package_trail', ['route' => true])) {
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

if (Yii::$app->user->can('app_package_delete', ['route' => true])) {
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
