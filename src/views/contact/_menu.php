<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 */

$items = [];

$items[] = [
    'label' => Yii::t('app', 'View'),
    'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
];
if (Yii::$app->user->can('app_contact_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update'
    ];
}


$jobItems = [];
if (Yii::$app->user->can('app_job_create', ['route' => true])) {
    $jobItems[] = [
        'label' => Yii::t('app', 'Create Job'),
        'url' => ['job/create', 'Job' => ['company_id' => $model->default_company_id, 'contact_id' => $model->id], 'ru' => ReturnUrl::getToken()],
    ];
}
if (Yii::$app->user->can('app_job_index', ['route' => true])) {
    $jobItems[] = [
        'label' => Yii::t('app', 'List Jobs'),
        'url' => ['job/index', 'JobSearch' => ['contact_id' => $model->id]],
    ];
}
if ($jobItems) {
    $items[] = [
        'label' => '<i class="fa fa-folder"></i> ' . Yii::t('app', 'Jobs'),
        'url' => ['job/index', 'JobSearch' => ['contact_id' => $model->id]],
        'encode' => false,
        'items' => $jobItems,
    ];
}

if (Yii::$app->user->can('app_contact_merge', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-compress"></i> ' . Yii::t('app', 'Merge'),
        'url' => ['merge', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'merge',
        'linkOptions' => [
            //'class' => 'modal-remote',
        ],
    ];
}
if ($model->hubSpotContact) {
    $hubSpotItems = [];
    $hubSpotItems[] = [
        'label' => Yii::t('app', 'HubSpot Page'),
        'url' => 'https://app.hubspot.com/sales/2659477/contact/' . $model->hubSpotContact->hub_spot_id . '/',
        'linkOptions' => [
            'target' => '_blank',
        ],
    ];
    $hubSpotItems[] = [
        'label' => Yii::t('app', 'HubSpot Pull'),
        'url' => ['hub-spot-pull', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    ];
    $items[] = [
        'label' => '<i class="fa fa-link"></i> ' . Yii::t('app', 'HubSpot'),
        'encode' => false,
        'items' => $hubSpotItems,
    ];
}

$auditItems = [];
if (Yii::$app->user->can('app_contact_log', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Log'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'log',
    ];
}
if (Yii::$app->user->can('app_contact_trail', ['route' => true])) {
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

if (Yii::$app->user->can('app_contact_delete', ['route' => true])) {
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
