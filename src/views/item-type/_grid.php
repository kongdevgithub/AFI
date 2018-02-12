<?php

use app\components\GridView;
use app\components\quotes\items\BaseItemQuote;
use app\models\ItemType;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ItemTypeSearch $searchModel
 */


$columns = [
    [
        'class' => 'yii\grid\ActionColumn',
        'urlCreator' => function ($action, $model, $key, $index) {
            /** @var ItemType $model */
            // using the column name as key, not mapping to 'id' like the standard generator
            $params = is_array($key) ? $key : [$model->primaryKey()[0] => (string)$key];
            $params[0] = Yii::$app->controller->id ? Yii::$app->controller->id . '/' . $action : $action;
            $params['ru'] = ReturnUrl::getToken();
            return Url::toRoute($params);
        },
        'template' => '{view} {update}',
        'headerOptions' => ['style' => 'width:30px'],
        'contentOptions' => ['nowrap' => 'nowrap']
    ],
    //'id',
    'name',
    [
        'attribute' => 'quote_class',
        'filter' => BaseItemQuote::opts(),
        'value' => function ($model) {
            /** @var ItemType $model */
            return $model->quote_class ? BaseItemQuote::opts()[$model->quote_class] : null;
        },
    ],
    [
        'attribute' => 'color',
        'value' => function ($model) {
            /** @var ItemType $model */
            return Html::tag('span', $model->color, [
                'class' => 'label label-default',
                'style' => 'color:#fff;background:' . $model->color,
            ]);
        },
        'format' => 'raw',
    ],
];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#item-type-searchModal',
]);
if (Yii::$app->user->can('app_item-type_create', ['route' => true])) {
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
        'heading' => Yii::t('app', 'Item Types'),
    ],
]);