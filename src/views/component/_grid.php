<?php

use app\components\GridView;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ComponentType;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ComponentSearch $searchModel
 */

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Component $model */
        $items = [];
        if (Yii::$app->user->can('app_component_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//component/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//component/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = 'code';
$columns[] = 'name';
$columns[] = 'brand';
$columns[] = [
    'attribute' => 'component_type_id',
    'value' => function ($model) {
        /** @var Component $model */
        return Html::a($model->componentType->name, ['/component-type/view', 'id' => $model->componentType->id, 'ru' => ReturnUrl::getToken()]);
    },
    'filter' => ArrayHelper::map(ComponentType::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'quote_class',
    'value' => function ($model) {
        /** @var Component $model */
        /** @var BaseComponentQuote $quote */
        $quote = new $model->quote_class;
        return '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>';
    },
    'filter' => BaseComponentQuote::opts(),
    'format' => 'raw',
];
$columns[] = 'unit_of_measure';
$columns[] = 'make_ready_cost';
if (Yii::$app->user->can('_view_cost_prices')) {
    $columns[] = 'unit_cost';
    $columns[] = 'minimum_cost';
    $columns[] = 'quantity_factor:ntext';
}
$columns[] = 'unit_weight';
$columns[] = 'track_stock';
$columns[] = 'quality_check';

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#component-searchModal',
]);
if (Yii::$app->user->can('app_component_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}
if (Yii::$app->user->can('app_component_export', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-download"></i> ' . Yii::t('app', 'Export'), ['component/export', 'ComponentSearch' => Yii::$app->request->get('ComponentSearch'), 'ru' => ReturnUrl::getToken()], [
        'title' => Yii::t('app', 'Export'),
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Components'),
    ],
]);
