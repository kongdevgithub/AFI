<?php

use app\components\GridView;
use app\models\Rollout;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\RolloutSearch $searchModel
 */

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Rollout $model */
        return Yii::$app->user->can('app_rollout_view') ? Html::a($model->id, ['/rollout/view', 'id' => $model->id]) : $model->id;
    },
    'format' => 'raw',
];
$columns[] = 'name';

$gridActions = [];
$gridActions [] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#rollout-searchModal',
]);
$gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
    'create',
    'ru' => ReturnUrl::getToken()
], ['class' => 'btn btn-default btn-xs']);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Rollouts'),
    ],
]);
