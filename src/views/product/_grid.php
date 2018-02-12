<?php

use app\components\GridView;
use app\models\Product;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ProductSearch $searchModel
 */

$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Product $model */
        $items = [];
        if (Yii::$app->user->can('app_product_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//product/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//product/view', 'id' => $model->id]),
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
    'attribute' => 'name',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'job_id',
    'value' => function ($model) {
        /** @var Product $model */
        return Html::a($model->job->name, ['//job/view', 'id' => $model->job->vid,], ['data-pjax' => 0]);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'product_type_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->productType ? $model->productType->name : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#product-searchModal',
]);
if (Yii::$app->user->can('app_product_export')) {
    $gridActions[] = Html::a('<i class="fa fa-download"></i> ' . Yii::t('app', 'Export'), ['product/export', 'ProductSearch' => Yii::$app->request->get('ProductSearch'), 'ru' => ReturnUrl::getToken()], [
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
        'heading' => Yii::t('app', 'Products'),
    ],
]);