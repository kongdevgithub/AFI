<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Rollout $model
 */

$items = [];

$items[] = [
    'label' => '<i class="fa fa-eye"></i> ' . Yii::t('app', 'View'),
    'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
    'encode' => false,
];
if (Yii::$app->user->can('app_rollout_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update',
    ];
}

$jobItems = [];
if (Yii::$app->user->can('app_job_create', ['route' => true])) {
    $jobItems[] = [
        'label' => Yii::t('app', 'Create Job'),
        'url' => ['job/create', 'Job' => ['rollout_id' => $model->id, 'company_id' => $model->company_id, 'contact_id' => $model->company->default_contact_id], 'ru' => ReturnUrl::getToken()],
        'encode' => false,
    ];
}
if (Yii::$app->user->can('app_job_index', ['route' => true])) {
    $jobItems[] = [
        'label' => Yii::t('app', 'List Jobs'),
        'url' => ['job/index', 'JobSearch' => ['rollout_id' => $model->id]],
        'encode' => false,
    ];
}
if ($jobItems) {
    $items[] = [
        'label' => '<i class="fa fa-folder"></i> ' . Yii::t('app', 'Jobs'),
        'url' => ['job/index', 'JobSearch' => ['rollout_id' => $model->id]],
        'encode' => false,
        'items' => $jobItems,
    ];
}

$auditItems = [];
if (Yii::$app->user->can('app_rollout_log', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Log'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'log',
    ];
}
if (Yii::$app->user->can('app_rollout_trail', ['route' => true])) {
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

if (Yii::$app->user->can('app_rollout_delete', ['route' => true])) {
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
