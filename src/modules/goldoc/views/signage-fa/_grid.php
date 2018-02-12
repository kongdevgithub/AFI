<?php

use app\components\GridView;
use app\components\ReturnUrl;
use app\modules\goldoc\models\SignageFa;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\SignageFaSearch $searchModel
 */
$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var SignageFa $model */
        return Yii::$app->user->can('app_signage-fa_view', ['route' => true]) ? Html::a($model->id, ['view', 'id' => $model->id]) : $model->id;
    },
    'format' => 'raw',
];
$columns[] = 'code';
$columns[] = 'comment';
$columns[] = 'sign_text:ntext';
$columns[] = 'goldoc_product_allocated';
$columns[] = 'material';
$columns[] = 'width';
$columns[] = 'height';
$columns[] = 'fixing';
$columns[] = 'venueQuantities';

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#signage-fa-searchModal',
]);
//if (Yii::$app->user->can('app_signage-fa_create', ['route' => true])) {
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
        'heading' => Yii::t('goldoc', 'Signage FAs'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);
