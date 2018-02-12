<?php

use app\models\Product;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $icon = $model->productType ? '<br>' . Html::img($model->productType->getImageSrc(), [
                'width' => 75,
                'height' => 75,
                'title' => $model->productType->getBreadcrumbString(' > '),
                'data-toggle' => 'tooltip',
            ]) : '';
        return $model->getLabel() . $icon;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'description',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        return $model->getDescription(['showItems' => false]);
    },
    'format' => 'raw',
];
$columns[] = [
    'header' => Yii::t('app', 'Size'),
    'attribute' => 'sizeHtml',
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'quantity',
    'hAlign' => 'center',
    'format' => 'raw',
];

echo GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => $model->getProducts(),
        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-products'],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => $columns,
    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => false,
    'showFooter' => false,
    'resizableColumns' => false,
]);