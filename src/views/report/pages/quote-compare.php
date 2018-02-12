<?php

use app\components\BulkQuoteHelper;
use app\components\MenuItem;
use kartik\grid\GridView;
use yii\bootstrap\Nav;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Quote Compare');
$this->params['nav'] = MenuItem::getReportsItems();

//$piqData = BulkQuoteHelper::piqData();

//echo \yii\helpers\VarDumper::export(array_keys(BulkQuoteHelper::getProducts()));die;

$jobs = [];
foreach (BulkQuoteHelper::getJobs() as $k => $job) {
    $jobs[$k] = 0;
}

$models = [];

$product_key = Yii::$app->request->get('product_key');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];

if (!$product_key) {
    $this->params['breadcrumbs'][] = Yii::t('app', 'Quote Compare');
    $products = BulkQuoteHelper::getProducts();
    $items = [];
    foreach ($products as $product_key => $product_data) {
        $items[] = [
            'label' => $product_key,
            'url' => ['report/index', 'report' => 'quote-compare', 'product_key' => $product_key],
        ];
    }
    echo Nav::widget([
        'items' => $items,
        'options' => ['class' => 'nav-pills'],
    ]);
    return;
}

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Quote Compare'), 'url' => ['report/index', 'report' => 'quote-compare']];
$this->params['breadcrumbs'][] = $product_key;


$product_data = BulkQuoteHelper::getProducts()[$product_key];

foreach (BulkQuoteHelper::getSizes($product_key) as $size_key => $size_data) {
    foreach (BulkQuoteHelper::getQuantities($product_key) as $quantity_key => $quantity_data) {
        $size = explode('x', $size_key);
        $model = [
            'product' => $product_key,
            'size' => $size_key . (isset($size[1]) ? '<br><small>area:' . (($size[0] * $size[1] / 1000 / 1000) . 'm<sup>2</sup></small><br><small>perimeter:' . (($size[0] + $size[1]) / 1000 * 2) . 'm') . '</small>' : ''),
            'quantity' => $quantity_key,
        ];
        foreach (BulkQuoteHelper::getSubstrates($product_key) as $substrate_key => $substrate_data) {
            $k = $product_key . ' ' . $substrate_key . ' ' . $size_key . ' x' . $quantity_key;
            //if (!isset($piqData[$k]) || $piqData[$k] == 0) {
            //    continue(2);
            //}
            $job = BulkQuoteHelper::getJob('TEST: ' . $k);
            $model[$substrate_key . '_v4'] = $job && $job->quote_generated == 1 ? Html::a(number_format(($job->quote_factor_price - ($job->quote_maximum_discount_price / $job->quote_markup)) / $quantity_key, 2), ['/job/quote', 'id' => $job->id]) : 0;
            //$model[$substrate_key . '_piq'] = isset($piqData[$k]) ? $piqData[$k] : 0;
            //$model[$substrate_key . '_diff'] = $job && $model[$substrate_key . '_piq'] ? ($job->quote_factor_price - ($job->quote_maximum_discount_price / $job->quote_markup)) / $quantity_key / $model[$substrate_key . '_piq'] : 0;

            //$style = '';
            //if ($model[$substrate_key . '_diff']) {
            //    if (abs($model[$substrate_key . '_diff'] - 1) > 0.05) {
            //        $style = 'color:blue';
            //    }
            //    if (abs($model[$substrate_key . '_diff'] - 1) > 0.1) {
            //        $style = 'color:orange';
            //    }
            //    if (abs($model[$substrate_key . '_diff'] - 1) > 0.2) {
            //        $style = 'color:red';
            //    }
            //}
            //$model[$substrate_key . '_diff'] = '<span style="' . $style . '">' . number_format($model[$substrate_key . '_diff'], 2) . '</span>';
        }
        $models[] = $model;
    }
}

$beforeHeaderColumns = [
    [
        'options' => ['colspan' => 3],
        'content' => 'Product',
    ],
];
$columns = [
    [
        'attribute' => 'product',
        'format' => 'raw',
        'group' => true,
    ],
    [
        'attribute' => 'size',
        'format' => 'raw',
        'group' => true,
        'subGroupOf' => 0,
    ],
    [
        'attribute' => 'quantity',
        'hAlign' => 'right',
        'format' => 'raw',
    ],
];
foreach (BulkQuoteHelper::getSubstrates($product_key) as $substrate_key => $substrate_data) {
    //$columns[] = [
    //    'attribute' => $substrate_key . '_piq',
    //    'header' => 'piq',
    //    'hAlign' => 'right',
    //    'format' => ['decimal', 2],
    //];
    $columns[] = [
        'attribute' => $substrate_key . '_v4',
        'header' => 'v4',
        'hAlign' => 'right',
        'format' => 'raw',
    ];
    //$columns[] = [
    //    'attribute' => $substrate_key . '_diff',
    //    'header' => 'diff',
    //    'hAlign' => 'right',
    //    'format' => 'raw',
    //];
    $beforeHeaderColumns[] = [
        'options' => [
            //'colspan' => 3,
            'class' => 'text-center',
        ],
        'content' => $substrate_key,
    ];
}

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $models,
        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-items'],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => $columns,
    'panel' => [
        'heading' => false,
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'beforeHeader' => [['columns' => $beforeHeaderColumns]],
    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => false,
]);
