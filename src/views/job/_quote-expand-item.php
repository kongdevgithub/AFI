<?php
/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

use app\components\fields\BaseField;
use app\components\fields\ComponentField;
use app\components\quotes\components\BaseComponentQuote;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;

$user = Yii::$app->user;

$models = $model->getMaterials();

$columns = [];
//$columns[] = [
//    'header' => '',
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
//    'value' => function ($model, $key, $index, $widget) {
//        return '<span class="label label-default">' . $model['id'] . '</span>';
//    },
//    'format' => 'raw',
//];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:200px;'],
    'attribute' => 'code',
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'name',
    'format' => 'raw',
];
//$columns[] = [
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
//    'header' => Yii::t('app', 'Class'),
//    'attribute' => 'quote_class',
//    'hAlign' => 'center',
//    'format' => 'raw',
//];
if ($user->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'MR Cost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'mr_cost',
        'hAlign' => 'right',
        'format' => 'raw',
    ];
    $columns[] = [
        'header' => Yii::t('app', 'UCost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'unit_cost',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
    ];
    $columns[] = [
        'header' => Yii::t('app', 'Min Cost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'minimum_cost',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
    ];
}
$columns[] = [
    'header' => Yii::t('app', 'Qty'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quantity',
    'hAlign' => 'right',
    'format' => 'raw',
];
$columns[] = [
    'header' => Yii::t('app', 'Per'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'unit_of_measure',
    'hAlign' => 'center',
    'format' => 'raw',
];
if ($user->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'Cost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'total_cost',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ];
    $columns[] = [
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'factor',
        //'value' => function ($model, $key, $index, $widget) {
        //    return Html::a($model['factor'], $model['update_url'], ['class' => 'modal-remote']);
        //},
        'hAlign' => 'right',
        'format' => 'raw',
    ];
    $columns[] = [
        'header' => Yii::t('app', 'IBase'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'total_price',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'pageSummary' => true,
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
    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
    'showPageSummary' => true,
    //'pjax' => true,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => false,
]);
