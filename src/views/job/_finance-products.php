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

        $itemTypes = [];
        foreach ($model->items as $item) {
            if ($item->quantity > 0) {
                if (!isset($itemTypes[$item->itemType->name])) {
                    $itemTypes[$item->itemType->name] = 0;
                }
                $itemTypes[$item->itemType->name] += $item->quantity * $model->quantity;
            }
        }
        $counts = [];
        foreach ($itemTypes as $itemType => $quantity) {
            $counts[] = Html::tag('span', $itemType . ':' . $quantity, ['class' => 'label label-default']);
        }
        $countString = $counts ? '<hr style="margin:0 5px">' . implode(' ', $counts) : '';

        return $model->getLabel() . $icon . $sizeString . $countString;
    },
    'format' => 'raw',
];
//$columns[] = [
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:200px;'],
//    'attribute' => 'name',
//];
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

        $margin = '';
        if (!$model->checkPriceMargin()) {
            $margin = '<br>' . Html::tag('span', Yii::t('app', 'The price for this Product has less than 30% margin!'), ['class' => 'label label-danger']);
        }

        $rate = $model->getRateLabel();
        if ($rate) {
            $rate = '<br>' . $rate;
        }

        $generating = '';
        if ($model->quote_generated != 1) {
            $generating = '<br><span class="label label-danger">' . Yii::t('app', 'Quote is being generated, please reload the page to check pricing.') . '</span>';
        }

        return $description . $margin . $rate . $generating;
    },
    'format' => 'raw',
];
//$columns[] = [
//    'header' => Yii::t('app', 'Size'),
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
//    'value' => function ($model, $key, $index, $widget) {
//        /** @var $model Product */
//        $size = [];
//        $area = $model->getArea();
//        if ($area) {
//            $size[] = ceil($area) . 'm<sup>2</sup>';
//        }
//        $perimeter = $model->getPerimeter();
//        if ($perimeter) {
//            $size[] = ceil($perimeter) . 'm';
//        }
//        return $model->getSizeHtml() . '<br>' . Html::tag('small', implode('&nbsp;|&nbsp;', $size));
//    },
//    'format' => 'raw',
//];
//$columns[] = [
//    'header' => Yii::t('app', 'Price'),
//    'headerOptions' => ['nowrap' => 'nowrap'],
//    'value' => function ($model, $key, $index, $widget) {
//        /** @var $model Product */
//        $attributes = [];
//        $attributes[] = [
//            'attribute' => 'staff_rep_id',
//            'value' => $model->staffRep ? $model->staffRep->getLink() : null,
//            'format' => 'raw',
//        ];
//        echo DetailView::widget([
//            'model' => $model,
//            'attributes' => $attributes,
//            'options' => ['class' => 'table table-condensed detail-view'],
//        ]);
//    },
//    'format' => 'raw',
//];
if (Y::user()->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'UCost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_unit_cost',
        'hAlign' => 'right',
        //'format' => ['decimal', 2],
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            return number_format($model->quote_unit_cost, 2);
        },
    ];
}
$columns[] = [
    'header' => Yii::t('app', 'UPrice'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quote_unit_price',
    'hAlign' => 'right',
    //'format' => ['decimal', 2],
    'format' => 'raw',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        return number_format($model->quote_quantity ? (($model->quote_factor_price - $model->quote_discount_price) * $model->job->quote_markup) / $model->quote_quantity : 0, 2);
    },
    'contentOptions' => ['nowrap' => 'nowrap'],
];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quantity',
    'label' => Yii::t('app', 'Qty'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        if (Y::user()->can('app_product_quantity', ['route' => true])) {
            return Html::a($model->quantity, ['/product/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote label label-default',
                'title' => Yii::t('app', 'Update Product Quantity'),
                'data-toggle' => 'tooltip',
            ]);
        }
        return Html::tag('span', $model->quantity, ['class' => 'label label-default']);
    },
    'hAlign' => 'center',
    'format' => 'raw',
];
if (Y::user()->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'Cost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_total_cost',
        'hAlign' => 'right',
        //'format' => ['decimal', 2],
        //'pageSummary' => true,
        'format' => ['decimal', 2],
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            return $model->quote_total_cost;
        },
        'pageSummary' => true,
    ];
}
//$columns[] = [
//    'header' => Yii::t('app', 'Markup'),
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
//    'hAlign' => 'right',
//    'value' => function ($model, $key, $index, $widget) {
//        /** @var $model Product */
//        return $model->quote_factor_price - $model->quote_total_cost;
//    },
//    'format' => ['decimal', 2],
//    'pageSummary' => true,
//];
if (Y::user()->can('staff')) {
    $columns[] = [
        'header' => Yii::t('app', 'PBase'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_total_price',
        'hAlign' => 'right',
        //'format' => ['decimal', 2],
        //'pageSummary' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            $locked = $model->preserve_unit_prices ? '<span class="fa fa-lock" title="' . number_format($model->quote_total_price_unlocked, 2) . '" data-toggle="tooltip"></span>&nbsp;' : '';
            return $locked . number_format($model->quote_total_price, 2);
        },
        'contentOptions' => ['nowrap' => 'nowrap'],
    ];
}
if (Y::user()->can('staff')) {
    $columns[] = [
        'header' => Yii::t('app', 'Factor'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_factor',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            return '<span class="label label-info">' . $model->quote_label . '</span> <span class="label label-default">x' . round($model->quote_factor, 4) . '</span>';
        },
        'hAlign' => 'center',
        'format' => 'raw',
    ];
}
if (Y::user()->can('staff')) {
    $columns[] = [
        'header' => Yii::t('app', 'Factored'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_factor_price',
        'hAlign' => 'right',
        //'format' => ['decimal', 2],
        //'pageSummary' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            return number_format($model->quote_factor_price, 2);
        }
    ];
}
if (Y::user()->can('staff')) {
    $columns[] = [
        'header' => Yii::t('app', 'Retail'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'hAlign' => 'right',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            $edit = '';
            if (Y::user()->can('app_product_price', ['route' => true])) {
                $edit = Html::a('<i class="fa fa-pencil"></i>', ['/product/price', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'modal-remote',
                        'title' => Yii::t('app', 'Update Price'),
                        'data-toggle' => 'tooltip',
                    ]) . '&nbsp;';
            }
            return $edit . number_format($model->quote_factor_price * $model->job->quote_markup, 2);
        },
        'contentOptions' => ['nowrap' => 'nowrap'],
    ];
}
if (Y::user()->can('staff')) {
    $columns[] = [
        'header' => Yii::t('app', 'Discount'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_discount_price',
        'hAlign' => 'right',
        //'format' => ['decimal', 2],
        //'pageSummary' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            $edit = '';
            if (Y::user()->can('app_product_discount', ['route' => true])) {
                $edit = Html::a('<i class="fa fa-pencil"></i>', ['/product/discount', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'modal-remote',
                        'title' => Yii::t('app', 'Update Discount'),
                        'data-toggle' => 'tooltip',
                    ]) . ' ';
            }
            return $edit . number_format($model->quote_discount_price * $model->job->quote_markup, 2);
        },
        'contentOptions' => ['nowrap' => 'nowrap'],
    ];
}

$columns[] = [
    'header' => Yii::t('app', 'Price'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'hAlign' => 'right',
    'format' => ['decimal', 2],
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        return ($model->quote_factor_price - $model->quote_discount_price) * $model->job->quote_markup;
    },
    'pageSummary' => true,
];


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
    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    'showPageSummary' => true,
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