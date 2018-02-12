<?php
/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

use app\models\Unit;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;


echo GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => $model->getUnits(),
        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-items'],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => [
        [
            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
            'attribute' => 'status',
            'value' => function ($model, $key, $index, $widget) {
                /** @var $model Unit */
                return $model->getStatusButton();
            },
            'hAlign' => 'center',
            'format' => 'raw',
        ],
        [
            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
            'attribute' => 'quantity',
            'hAlign' => 'right',
            'format' => 'raw',
        ],
        [
            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
            'attribute' => 'package_id',
            'format' => 'raw',
        ],
    ],
    'panel' => [
        'heading' => false,
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'pjax' => true,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => false,
]);
