<?php

use app\components\GridView;
use app\models\Item;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ItemSearch $searchModel
 */

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Item $model */
        $items = [];
        if (Yii::$app->user->can('app_item_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//item/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//item/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'headerOptions' => ['style' => 'width:120px;'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Item $model */
        return $model->getStatusButtons();
    },
    //'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'filter' => false,
    'headerOptions' => ['style' => 'width:100px;'],
    'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'name',
    'value' => function ($model) {
        /** @var Item $model */
        return $model->name;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'product_id',
    'value' => function ($model) {
        /** @var Item $model */
        return $model->product->name;
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Job'),
    'value' => function ($model) {
        /** @var Item $model */
        return $model->product->job->name;
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Company'),
    'value' => function ($model) {
        /** @var Item $model */
        return $model->product->job->company->name;
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Contact'),
    'value' => function ($model) {
        /** @var Item $model */
        return $model->product->job->contact->getLabel(true);
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'item_type_id',
    'value' => function ($model) {
        /** @var Item $model */
        return Html::tag('span', $model->itemType->name, ['class' => 'label label-info']);
    },
    'headerOptions' => ['style' => 'width:150px;'],
    'contentOptions' => ['class' => 'text-center'],
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'quantity',
    'value' => function ($model) {
        /** @var Item $model */
        return Html::tag('span', $model->quantity * $model->product->quantity, ['class' => 'label label-default']);
    },
    'headerOptions' => ['style' => 'width:100px;'],
    'contentOptions' => ['class' => 'text-center'],
    'format' => 'raw',
];


$gridActions = [];
$gridActions [] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#item-searchModal',
]);
if (Yii::$app->user->can('app_item_export')) {
    $gridActions [] = Html::a('<i class="fa fa-download"></i> ' . Yii::t('app', 'Export'), ['item/export', 'ItemSearch' => Yii::$app->request->get('ItemSearch'), 'ru' => ReturnUrl::getToken()], [
        'title' => Yii::t('app', 'Export'),
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
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