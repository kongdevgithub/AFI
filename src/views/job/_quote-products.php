<?php

use app\components\GridView;
use app\models\Component;
use app\models\Option;
use app\models\Product;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$dataProvider = new ActiveDataProvider([
    'query' => $model->getProducts(),
    'pagination' => [
        'pageParam' => 'page-products',
    ],
    'sort' => false,
]);

$columns = [];
$columns[] = [
    'class' => 'kartik\grid\ExpandRowColumn',
    'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
    },
    'detail' => function ($model, $key, $index, $column) {
        /** @var $model Product */
        return Yii::$app->controller->renderPartial('_quote-expand-product', ['model' => $model]);
    },
    'detailRowCssClass' => '',
    'allowBatchToggle' => true,
    'expandOneOnly' => false,
];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'id',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */

        $items = [];
        $items[] = $model->getLabel() . '<br>';
        if (Y::user()->can('app_product_update', ['route' => true])) {
            $items[] = Html::a('<i class="fa fa-pencil"></i>', ['/product/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Update'),
                'data-toggle' => 'tooltip',
            ]);
        }
        if (Y::user()->can('app_product_copy', ['route' => true])) {
            $items[] = Html::a('<i class="fa fa-copy"></i>', ['/product/copy', 'id' => $model->id], [
                'title' => Yii::t('app', 'Copy'),
                'data-toggle' => 'tooltip',
            ]);
        }
        if (Y::user()->can('app_product_split', ['route' => true])) {
            $items[] = Html::a('<i class="fa fa-th-list"></i>', ['/product/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Split'),
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-toggle' => 'tooltip',
            ]);
        }
        if (Y::user()->can('app_product_delete', ['route' => true])) {
            $items[] = Html::a('<span class="fa fa-trash"></span>', ['/product/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Delete'),
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
                'data-toggle' => 'tooltip',
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

        return implode('&nbsp;&nbsp;', $items) . $icon . $sizeString . $countString;
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
            $values = [];
            $values[] = number_format($model->quote_unit_cost, 2);
            if ($model->forkQuantityProducts) {
                //$values[] = '';
                foreach ($model->forkQuantityProducts as $_product) {
                    $values[] = number_format($_product->quote_unit_cost, 2);
                }
            }
            return implode('<br>', $values);
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
        if (Y::user()->can('app_product_quantity', ['route' => true])) {
            $output[] = Html::a($model->quantity, ['/product/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
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
            if (Y::user()->can('app_product_fork-quantity', ['route' => true])) {
                $output[] = Html::a('<i class="fa fa-pencil"></i>', ['/product/fork-quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'class' => 'modal-remote label label-default',
                    'title' => Yii::t('app', 'Fork Quantity'),
                    'data-toggle' => 'tooltip',
                ]);
            }
        }
        return implode('<br>', $output);
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
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Product */
            $values = [];
            $values[] = number_format($model->quote_total_cost, 2);
            if ($model->forkQuantityProducts) {
                //$values[] = '';
                foreach ($model->forkQuantityProducts as $_product) {
                    $values[] = number_format($_product->quote_total_cost, 2);
                }
            }
            return implode('<br>', $values);
        }
    ];
}
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
            $values = [];
            $locked = $model->preserve_unit_prices ? '<span class="fa fa-lock" title="' . number_format($model->quote_total_price_unlocked, 2) . '" data-toggle="tooltip"></span>&nbsp;' : '';
            $values[] = $locked . number_format($model->quote_total_price, 2);
            if ($model->forkQuantityProducts) {
                //$values[] = '';
                foreach ($model->forkQuantityProducts as $_product) {
                    $values[] = number_format($_product->quote_total_price, 2);
                }
            }
            return implode('<br>', $values);
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
            $values = [];
            $values[] = number_format($model->quote_factor_price, 2);
            if ($model->forkQuantityProducts) {
                //$values[] = '';
                foreach ($model->forkQuantityProducts as $_product) {
                    $values[] = number_format($_product->quote_factor_price, 2);
                }
            }
            return implode('<br>', $values);
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
            $values = [];
            $values[] = $edit . number_format($model->quote_factor_price * $model->job->quote_markup, 2);
            if ($model->forkQuantityProducts) {
                foreach ($model->forkQuantityProducts as $_product) {
                    $edit = '';
                    if (Y::user()->can('app_product_price', ['route' => true])) {
                        $edit = Html::a('<i class="fa fa-pencil"></i>', ['/product/price', 'id' => $_product->id, 'ru' => ReturnUrl::getToken()], [
                                'class' => 'modal-remote',
                                'title' => Yii::t('app', 'Update Price'),
                                'data-toggle' => 'tooltip',
                            ]) . '&nbsp;';
                    }
                    $values[] = $edit . number_format($_product->quote_factor_price * $_product->job->quote_markup, 2);
                }
            }
            return implode('<br>', $values);
        },
        'contentOptions' => ['nowrap' => 'nowrap'],
    ];
}
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
        $values = [];
        $values[] = $edit . number_format($model->quote_discount_price * $model->job->quote_markup, 2);
        if ($model->forkQuantityProducts) {
            foreach ($model->forkQuantityProducts as $_product) {
                $edit = '';
                if (Y::user()->can('app_product_discount', ['route' => true])) {
                    $edit = Html::a('<i class="fa fa-pencil"></i>', ['/product/discount', 'id' => $_product->id, 'ru' => ReturnUrl::getToken()], [
                            'class' => 'modal-remote',
                            'title' => Yii::t('app', 'Update Discount'),
                            'data-toggle' => 'tooltip',
                        ]) . ' ';
                }
                $values[] = $edit . number_format($_product->quote_discount_price * $_product->job->quote_markup, 2);
            }
        }
        return implode('<br>', $values);
    },
    'contentOptions' => ['nowrap' => 'nowrap'],
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

$gridActions = [];
if (Y::user()->can('app_product_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Product'), [
        'product/create',
        'Product' => ['job_id' => $model->id],
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs',
    ]);
}
if (Y::user()->can('app_product_bulk-components', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Component Products'), [
        'product/bulk-components',
        'Product' => ['job_id' => $model->id],
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-default btn-xs',
    ]);
}

echo GridView::widget([
    'id' => 'product-grid',
    'dataProvider' => $dataProvider,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Products'),
    ],
]);