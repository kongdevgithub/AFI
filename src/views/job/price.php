<?php

use app\models\Address;
use app\models\Component;
use app\models\Option;
use app\models\Package;
use app\models\Product;
use cornernote\shortcuts\Y;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-price">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>

            <?php

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
                ];
            }
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
            $columns[] = [
                'header' => Yii::t('app', 'Retail'),
                'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                'hAlign' => 'right',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $widget) {
                    /** @var $model Product */
                    return Html::textInput('Product[' . $model->id . '][price]', round($model->quote_factor_price * $model->job->quote_markup, 2), ['style' => 'width:80px;text-align:right;']);
                },
                'contentOptions' => ['nowrap' => 'nowrap'],
            ];
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
                    return number_format($model->quote_discount_price * $model->job->quote_markup, 2);
                },
                'contentOptions' => ['nowrap' => 'nowrap'],
            ];

            $columns[] = [
                'header' => Yii::t('app', 'Price'),
                'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                'hAlign' => 'right',
                'value' => function ($model, $key, $index, $widget) {
                    /** @var $model Product */
                    return number_format(($model->quote_factor_price - $model->quote_discount_price) * $model->job->quote_markup, 2);
                },
                'format' => 'raw',
            ];
            ?>


            <?php $form = ActiveForm::begin(['id' => 'JobPrice']); ?>
            <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

            <?php
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
                //'showFooter' => true,
                'footerRowOptions' => ['class' => 'hide'],
            ]);
            ?>

            <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Update Prices'), [
                'id' => 'save-prices-' . $model->formName(),
                'class' => 'btn btn-success'
            ]); ?>

            <?php ActiveForm::end(); ?>



        </div>
        <div class="col-md-3">
            <?= $this->render('/job/_status-box', ['model' => $model]) ?>
            <?= $this->render('/job/_quote-version-fork', ['model' => $model]) ?>
            <?= $this->render('/job/_job-copy', ['model' => $model]) ?>
            <?= $this->render('/job/_job-redo', ['model' => $model]) ?>
            <?= $this->render('/job/_notes', ['model' => $model]) ?>
            <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Attachments')]) ?>
        </div>
    </div>

</div>
