<?php

use app\models\Component;
use app\models\Option;
use app\models\Product;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$columns = [];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'id',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */

        $items = [];
        $items[] = Html::tag('span', 'product-' . $model->id, ['class' => 'label label-default']) . '<br>';
        if ($model->job->status == 'job/draft') {
            $items[] = Html::a('<i class="fa fa-pencil"></i>', ['product/update', 'id' => $model->id], [
                'title' => Yii::t('app', 'Update'),
                'data-toggle' => 'tooltip',
                //'data-pjax' => 0,
            ]);
            $items[] = Html::a('<i class="fa fa-th-list"></i>', ['/product/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Split'),
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-toggle' => 'tooltip',
            ]);
        }
        $items[] = Html::a('<i class="fa fa-copy"></i>', ['product/copy', 'id' => $model->id], [
            'title' => Yii::t('app', 'Copy'),
            'data-toggle' => 'tooltip',
            //'data-pjax' => 0,
        ]);
        if ($model->job->status == 'job/draft') {
            $items[] = Html::a('<span class="fa fa-trash"></span>', ['product/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Delete'),
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
                'data-toggle' => 'tooltip',
                //'data-pjax' => 0,
            ]);
        }

        $icon = $model->productType ? '<br>' . Html::img($model->productType->getImageSrc(), [
                'width' => 75,
                'height' => 75,
                'title' => $model->productType->getBreadcrumbString(' > '),
                'data-toggle' => 'tooltip',
            ]) : '';

        $size = [];
        $area = $model->getArea();
        if ($area) {
            $size[] = ceil($area) . 'm<sup>2</sup>';
        }
        $perimeter = $model->getPerimeter();
        if ($perimeter) {
            $size[] = ceil($perimeter) . 'm';
        }
        $sizeString = '<hr style="margin:0 5px">' . Html::tag('span', $model->getSizeHtml(), ['class' => 'label label-default']) . ' ' . Html::tag('span', implode('&nbsp;|&nbsp;', $size), ['class' => 'label label-default']);

        return implode('&nbsp;&nbsp;', $items) . $icon . $sizeString;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'description',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $description = $model->getDescription([
            'showItems' => !$model->quote_hide_item_description,
            'itemDescriptionOptions' => [
                'forceOptions' => [
                    ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                ],
            ],
        ]);

        $generating = '';
        if ($model->quote_generated != 1) {
            $generating = '<br><span class="label label-danger">' . Yii::t('app', 'Quote is being generated, please reload the page to check pricing.') . '</span>';
        }

        return $description . $generating;
    },
    'format' => 'raw',
];
$columns[] = [
    'header' => Yii::t('app', 'UPrice'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quote_unit_price',
    'hAlign' => 'right',
    //'format' => ['decimal', 2],
    'format' => 'raw',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $values = [];
        //$values[] = number_format($model->quote_unit_price, 2);
        $values[] = number_format($model->quote_quantity ? (($model->quote_factor_price - $model->quote_discount_price) * $model->job->quote_markup) / $model->quote_quantity : 0, 2);
        if ($model->forkQuantityProducts) {
            //$values[] = '';
            foreach ($model->forkQuantityProducts as $_product) {
                $values[] = number_format($_product->quote_quantity ? (($_product->quote_factor_price - $_product->quote_discount_price) * $_product->job->quote_markup) / $_product->quote_quantity : 0, 2);
            }
        }
        return implode('<br>', $values);
    },
    'contentOptions' => ['nowrap' => 'nowrap'],
];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quantity',
    'label' => Yii::t('app', 'Qty'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $output = [];
        if ($model->job->status == 'job/draft') {
            $output[] = Html::a($model->quantity, ['product/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote label label-default',
                'title' => Yii::t('app', 'Update Product Quantity'),
                'data-toggle' => 'tooltip',
            ]);
        } else {
            $output[] = Html::tag('span', $model->quantity, ['class' => 'label label-default']);
        }
        foreach ($model->forkQuantityProducts as $_product) {
            $output[] = $_product->quantity;
        }
        if ($model->job->status == 'job/draft') {
            $output[] = Html::a('<i class="fa fa-pencil"></i>', ['product/fork-quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote label label-default',
                'title' => Yii::t('app', 'Fork Quantity'),
                'data-toggle' => 'tooltip',
            ]);
        }
        return implode('<br>', $output);
    },
    'hAlign' => 'center',
    'format' => 'raw',
];
$columns[] = [
    'header' => Yii::t('app', 'Price'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'hAlign' => 'right',
    'format' => 'raw',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $values = [];
        $values[] = number_format(($model->quote_factor_price - $model->quote_discount_price) * $model->job->quote_markup, 2);
        if ($model->forkQuantityProducts) {
            foreach ($model->forkQuantityProducts as $_product) {
                $values[] = number_format(($_product->quote_factor_price - $_product->quote_discount_price) * $_product->job->quote_markup, 2);
            }
        }
        return implode('<br>', $values);
    }
];

$createProductLink = '';
if ($model->status == 'job/draft') {
    $createProductLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Product'), [
        'product/create',
        'Product' => ['job_id' => $model->id],
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs',
    ]);
}

echo GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => $model->getProducts(),
        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-products'],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Products'),
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => '<div class="pull-right">' . $createProductLink . '</div><h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    //'showPageSummary' => true,
    //'pjax' => true,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    //'containerOptions' => ['style' => 'overflow: hidden'],
    'hover' => false,
    //'floatHeader' => true,
    //'floatHeaderOptions' => ['scrollingTop' => 0],
    'showFooter' => true,
    'footerRowOptions' => ['class' => 'hide'],
]);