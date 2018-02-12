<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */

use app\models\Component;
use app\models\Item;
use app\models\Option;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

$user = Yii::$app->user;


$columns = [];

$columns[] = [
    'class' => 'kartik\grid\ExpandRowColumn',
    'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
    },
    'detail' => function ($model, $key, $index, $column) {
        /** @var $model Item */
        return Yii::$app->controller->renderPartial('_quote-expand-item', ['model' => $model]);
    },
    //'detailUrl' => ['job/index'],
    'detailRowCssClass' => '',
    'expandOneOnly' => false,
];
$columns[] = [
    'attribute' => 'id',
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Item */
        $artwork = '';
        if ($model->artwork) {
            $thumb = $model->artwork->getFileUrl('100x100');
            $image = $model->artwork->getFileUrl('800x800');
            $artwork = '<br>' . Html::a(Html::img($thumb), $image, ['data-fancybox' => 'gallery-' . $model->artwork->id]);
        }

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

        $links = [];
        if (Y::user()->can('app_item_shipping-address-quantity', ['route' => true])) {
            $links[] = Html::a('<i class="fa fa-truck"></i>', ['/item/shipping-address-quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote',
                'title' => Yii::t('app', 'Shipping Address Quantity'),
                'data-toggle' => 'tooltip',
            ]);
        }
        if (Y::user()->can('app_item_split', ['route' => true])) {
            if ($model->split_id) {
                $links[] = Html::a('<i class="fa icon-merge"></i>', ['/item/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'title' => Yii::t('app', 'Merge'),
                    'data-toggle' => 'tooltip',
                    'data-confirm' => Yii::t('app', 'Are you sure?'),
                    'data-method' => 'post',
                ]);
                if (Y::user()->can('app_item_split-parent', ['route' => true])) {
                    $items[] = Html::a('<i class="fa fa-dot-circle-o"></i>', ['/item/split-parent', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'title' => Yii::t('app', 'Make Parent of Split'),
                        'data-toggle' => 'tooltip',
                        'data-confirm' => Yii::t('app', 'Are you sure?'),
                        'data-method' => 'post',
                    ]);
                }
            } elseif ($model->quantity * $model->product->quantity > 1) {
                $links[] = Html::a('<i class="fa icon-split"></i>', ['/item/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'class' => 'modal-remote',
                    'title' => Yii::t('app', 'Split'),
                    'data-toggle' => 'tooltip',
                ]);
            }
        }
        $links = $links ? '<br>' . implode(' &nbsp;', $links) : '';

        return $model->getLabel() . $links . $artwork . $sizeString;
        //return implode(' &nbsp;', [
        //    $model->getLabel() . '<br>',
        //    Html::a('<i class="fa fa-pencil"></i>', ['/item/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()]),
        //    Html::a('<span class="fa fa-trash"></span>', ['/item/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
        //        'data-confirm' => Yii::t('app', 'Are you sure?'),
        //        'data-method' => 'post',
        //    ]),
        //]);
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'name',
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Item */
        $size = '';
        if ($model->checkShowSize()) {
            $size = ' - ' . $model->getSizeHtml();
        }

        $description = $model->getDescription([
            'forceOptions' => [
                ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
            ],
        ]);

        $change = '';
        if (explode('/', $model->status)[1] == 'change') {
            $content = Yii::t('app', 'Change Request by') . ' ' . $model->change_requested_by . ':<br>' . Yii::$app->formatter->asNtext($model->change_request_details);
            $change = Html::tag('div', $content, [
                    'class' => 'alert alert-danger',
                ]) . '<br>';
        }

        return Html::encode($model->name) . $size . $description . $change;
    },
    'format' => 'raw',
];
if ($user->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'UCost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_unit_cost',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
    ];
}
$columns[] = [
    'header' => Yii::t('app', 'UPrice'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quote_unit_price',
    'value' => function ($model, $key, $index, $widget) use ($user) {
        /** @var $model Item */
        if ($user->can('_view_cost_prices')) {
            return $model->quote_unit_price;
        }
        return $model->quantity ? $model->quote_factor_price / $model->quantity : 0;
    },
    'hAlign' => 'right',
    'format' => ['decimal', 2],
];
$columns[] = [
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quantity',
    'label' => Yii::t('app', 'Qty'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Item */
        if (Y::user()->can('app_item_quantity', ['route' => true])) {
            return Html::a($model->quantity * $model->product->quantity, ['/item/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote label label-default',
                'title' => Yii::t('app', 'Update Item Quantity'),
                'data-toggle' => 'tooltip',
            ]);
        }
        return Html::tag('span', $model->quantity * $model->product->quantity, [
            'class' => 'label label-default',
        ]);
    },
    'hAlign' => 'center',
    'format' => 'raw',
];
if ($user->can('_view_cost_prices')) {
    $columns[] = [
        'header' => Yii::t('app', 'Cost'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_total_cost',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ];
//$columns[] = [
//    'header' => Yii::t('app', 'Markup'),
//    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
//    'hAlign' => 'right',
//    'value' => function ($model, $key, $index, $widget) {
//        /** @var $model Item */
//        return $model->quote_factor_price - $model->quote_total_cost;
//    },
//    'format' => ['decimal', 2],
//    'pageSummary' => true,
//];
    $columns[] = [
        'header' => Yii::t('app', 'IBase'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_total_price',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ];
    $columns[] = [
        'header' => Yii::t('app', 'Factor'),
        'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
        'attribute' => 'quote_factor',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Item */
            return '<span class="label label-info">' . $model->quote_label . '</span> <span class="label label-default">x' . ($model->quote_factor + 0) . '</span>';
        },
        'hAlign' => 'center',
        'format' => 'raw',
    ];
}
$columns[] = [
    'header' => Yii::t('app', 'PBase'),
    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
    'attribute' => 'quote_factor_price',
    'hAlign' => 'right',
    'format' => ['decimal', 2],
    'pageSummary' => true,
];
?>

<div class="kv-detail-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9">
                <?php
                echo GridView::widget([
                    'dataProvider' => new ActiveDataProvider([
                        'query' => $model->getItems()->andWhere(['>', 'quantity', 0]),
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
                    //'showPageSummary' => true,
                    //'pjax' => true,
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]);
                ?>
            </div>
            <div class="col-md-3">
                <?= $this->render('/note/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Notes')]) ?>
                <?= $this->render('/link/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Links')]) ?>
                <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Attachments')]) ?>
            </div>
        </div>
    </div>
</div>
