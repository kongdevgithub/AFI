<?php

use app\components\GridView;
use app\components\ReturnUrl;
use app\models\Carrier;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CarrierSearch $searchModel
 */

$this->title = Yii::t('app', 'Carriers');
//$this->params['breadcrumbs'][] = $this->title;

$columns = [];

$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Carrier $model */
        $items = [];
        if (Yii::$app->user->can('app_carrier_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//carrier/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//carrier/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'headerOptions' => ['style' => 'width:100px;'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'name',
    'value' => function ($model) {
        /** @var Carrier $model */
        return $model->name;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'my_freight_code',
    'value' => function ($model) {
        /** @var Carrier $model */
        return $model->my_freight_code;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'cope_freight_code',
    'value' => function ($model) {
        /** @var Carrier $model */
        return $model->cope_freight_code;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'tracking_url',
    'value' => function ($model) {
        /** @var Carrier $model */
        return $model->tracking_url;
    },
    'format' => 'raw',
];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#carrier-searchModal',
]);
if (Yii::$app->user->can('app_carrier_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Carriers'),
    ],
]);
