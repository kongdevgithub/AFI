<?php

use app\components\GridView;
use app\components\ReturnUrl;
use app\modules\goldoc\models\SignageWayfinding;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\SignageWayfindingSearch $searchModel
 */
$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var SignageWayfinding $model */
        return Yii::$app->user->can('app_signage-wayfinding_view', ['route' => true]) ? Html::a($model->id, ['view', 'id' => $model->id]) : $model->id;
    },
    'format' => 'raw',
];
$columns[] = 'batch';
$columns[] = 'quantity';
$columns[] = 'sign_id';
$columns[] = 'sign_code';
$columns[] = 'level';
$columns[] = 'message_side_1:ntext';
$columns[] = 'message_side_2:ntext';
$columns[] = 'fixing';
$columns[] = 'notes';

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#signage-wayfinding-searchModal',
]);
//if (Yii::$app->user->can('app_signage-wayfinding_create', ['route' => true])) {
//    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
//        'create',
//        'ru' => ReturnUrl::getToken()
//    ], ['class' => 'btn btn-default btn-xs']);
//}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    //'multiActions' => $multiActions,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('goldoc', 'Signage Wayfindings'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);
