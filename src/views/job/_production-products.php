<?php

use app\components\GridView;
use app\models\Product;
use app\components\ReturnUrl;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$columns = [];
$columns[] = [
    'class' => 'kartik\grid\ExpandRowColumn',
    'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_EXPANDED;
    },
    'detail' => function ($model, $key, $index, $column) {
        /** @var $model Product */
        return Yii::$app->controller->renderPartial('_production-expand-product', ['model' => $model]);
    },
    'detailRowCssClass' => '',
    'allowBatchToggle' => true,
    'expandOneOnly' => false,
];
$columns[] = [
    'attribute' => 'id',
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        $items = [];
        $items[] = $model->getLabel() . '<br>';
        //if (Y::user()->can('app_product_update', ['route' => true])) {
        //    $items[] = Html::a('<i class="fa fa-pencil"></i>', ['/product/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
        //        'title' => Yii::t('app', 'Update'),
        //        'data-toggle' => 'tooltip',
        //        //'data-pjax' => 0,
        //    ]);
        //}
        //if (Y::user()->can('app_product_copy', ['route' => true])) {
        //    $items[] = Html::a('<i class="fa fa-copy"></i>', ['/product/copy', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
        //        'title' => Yii::t('app', 'Copy'),
        //        'data-toggle' => 'tooltip',
        //        //'data-pjax' => 0,
        //    ]);
        //}
        //if (Y::user()->can('app_product_split', ['route' => true])) {
        //    $items[] = Html::a('<i class="fa fa-th-list"></i>', ['/product/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
        //        'title' => Yii::t('app', 'Split'),
        //        'data-confirm' => Yii::t('app', 'Are you sure?'),
        //        'data-toggle' => 'tooltip',
        //        //'data-pjax' => 0,
        //    ]);
        //}
        //if (Y::user()->can('app_product_delete', ['route' => true])) {
        //    $items[] = Html::a('<span class="fa fa-trash"></span>', ['/product/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
        //        'title' => Yii::t('app', 'Delete'),
        //        'data-confirm' => Yii::t('app', 'Are you sure?'),
        //        'data-method' => 'post',
        //        'data-toggle' => 'tooltip',
        //        //'data-pjax' => 0,
        //    ]);
        //}

        $status = '<hr style="margin: 5px 0;">' . $model->getStatusButtons();;

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

        return implode(' &nbsp;', $items) . $status . $sizeString;
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
    'attribute' => 'quantity',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
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
        return implode(' ', $counts);
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Type'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Product */
        return $model->productType ? '<br>' . Html::img($model->productType->getImageSrc(), [
                'width' => 75,
                'height' => 75,
                'title' => $model->productType->getBreadcrumbString(' > '),
                'data-toggle' => 'tooltip',
            ]) : '';
    },
    'format' => 'raw',
];
//$columns[] = [
//    'header' => Yii::t('app', 'Size'),
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
//    'attribute' => 'sizeHtml',
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
//    'attribute' => 'quantity',
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
//    'label' => Yii::t('app', 'Qty'),
//    'value' => function ($model, $key, $index, $widget) {
//        /** @var $model Product */
//        if ($model->job->status == 'job/draft') {
//            return Html::a($model->quantity, ['/product/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
//                'class' => 'modal-remote label label-default',
//                'title' => Yii::t('app', 'Update Product Quantity'),
//                'data-toggle' => 'tooltip',
//            ]);
//        }
//        return Html::tag('span', $model->quantity, [
//            'class' => 'label label-default',
//        ]);
//    },
//    'hAlign' => 'center',
//    'format' => 'raw',
//];

$grid_id = 'job-production-products';

$multiActions = [
    [
        'label' => Yii::t('app', 'Progress Items'),
        'url' => ['/item/progress', 'ru' => ReturnUrl::getToken()],
    ],
];

$dataProvider = new ActiveDataProvider([
    'query' => $model->getProducts(),
    'pagination' => [
        'pageParam' => 'page-products',
    ],
    'sort' => false,
]);

echo GridView::widget([
    'id' => $grid_id,
    'dataProvider' => $dataProvider,
    'multiActions' => $multiActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Products'),
    ],
]);

if ($dataProvider->totalCount) {
    $this->registerJs("jQuery('#$grid_id').yiiGridView('setSelectionColumn', " . Json::encode([
            'name' => 'check',
            'multiple' => true,
            'checkAll' => false,
        ]) . ");");
}