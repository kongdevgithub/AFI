<?php

use app\components\fields\BaseField;
use app\components\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\OptionSearch $searchModel
 */


$columns = [
    [
        'class' => 'yii\grid\ActionColumn',
        'urlCreator' => function ($action, $model, $key, $index) {
            /** @var app\models\Option $model */
            // using the column name as key, not mapping to 'id' like the standard generator
            $params = is_array($key) ? $key : [$model->primaryKey()[0] => (string)$key];
            $params[0] = Yii::$app->controller->id ? Yii::$app->controller->id . '/' . $action : $action;
            $params['ru'] = ReturnUrl::getToken();
            return Url::toRoute($params);
        },
        'template' => '{view} {update}',
        'headerOptions' => ['style' => 'width:30px'],
        'contentOptions' => ['nowrap' => 'nowrap'],
    ],
    'name',
    [
        'attribute' => 'field_class',
        'filter' => BaseField::opts(),
        'value' => function ($model) {
            return $model->field_class ? BaseField::opts()[$model->field_class] : null;
        },
    ],
];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#option-searchModal',
]);
if (Yii::$app->user->can('app_option_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('app', 'Options'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);