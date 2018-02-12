<?php

use app\components\GridView;
use app\models\Package;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\PackageSearch $searchModel
 */

$columns = [];
$columns[] = [
    'class' => 'kartik\grid\CheckboxColumn',
];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Package $model */
        $items = [];
        if (Yii::$app->user->can('app_package_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//package/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//package/view', 'id' => $model->id]),
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
        /** @var Package $model */
        return $model->getStatusButtons();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'pickup_id',
    'value' => function ($model) {
        /** @var Package $model */
        $output = [];
        if ($model->pickup) {
            $output[] = $model->pickup->getLink();
            if ($model->pickup->carrier) {
                $output[] = $model->pickup->carrier->getLink();
            }
            if ($model->pickup->carrier_ref) {
                $output[] = $model->pickup->getTrackingLink();
            }
            if ($model->pickup->collected_at) {
                $output[] = Yii::$app->formatter->asDatetime($model->pickup->collected_at);
            }
        }
        return implode('<br>', $output);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'address',
    'label' => Yii::t('app', 'Address'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Package */
        return $model->getAddressLabel();
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Dimensions'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Package */
        return $model->getDimensionsLabel();
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Items'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Package */
        return $this->render('/package/_item_quantity', ['model' => $model]);
    },
    'format' => 'raw',
];

$multiActions = [
    [
        'label' => Yii::t('app', 'Address'),
        'url' => ['package/address', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Dimensions'),
        'url' => ['package/dimensions', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Pickup'),
        'url' => ['package/pickup', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Print'),
        'url' => ['package/print', 'ru' => ReturnUrl::getToken()],
    ],
];

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#package-searchModal',
]);
if (Yii::$app->user->can('app_package_create', ['route' => true])) {
    $gridActions[] = Html::a(
        '<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'multiActions' => $multiActions,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Items'),
    ],
]);

