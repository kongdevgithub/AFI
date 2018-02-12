<?php

use app\components\GridView;
use app\models\Pickup;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\PickupSearch $searchModel
 */

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Pickup $model */
        $items = [];
        if (Yii::$app->user->can('app_pickup_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//pickup/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//pickup/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Pickup $model */
        return $model->getStatusButtons();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'carrier_id',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Pickup */
        if ($model->carrier) {
            return $model->carrier->name . '<br>' . $model->getTrackingLink();
        }
        return '';
    },
    'filter' => false,
    'contentOptions' => ['style' => 'width:100px'],
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Dates'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Pickup */
        $logDates = [];
        foreach (['collected_at', 'emailed_at', 'pod_date'] as $field) {
            if (!empty($model->$field)) {
                $logDates[] = Yii::$app->formatter->asDatetime($model->$field) . '&nbsp;' . Inflector::humanize(str_replace(['_at', '_date'], '', $field), true);
            }
        }
        return implode('<br>', $logDates);
    },
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:150px'],
];
$columns[] = [
    'label' => Yii::t('app', 'Packages'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Pickup */
        return $this->render('/pickup/_packages', ['model' => $model, 'showUnits' => false]);
    },
    'format' => 'raw',
];

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#pickup-searchModal',
]);
if (Yii::$app->user->can('app_pickup_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Items'),
    ],
]);
