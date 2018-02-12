<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/71f573f8a24512e6aac3b417da853ba3
 *
 * @package default
 */


use app\components\GridView;
use app\components\ReturnUrl;
use app\models\CompanyRate;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CompanyRateSearch $searchModel
 */
$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if (Yii::$app->user->can('app_company-rate_view', ['route' => true])) {
            return Html::a($model->id, ['//company-rate/view', 'id' => $model->id,]);
        }
        return $model->id;
    },
    'format' => 'raw',
    //'enableSorting' => false,
];
$columns[] = [
    'class' => yii\grid\DataColumn::className(),
    'attribute' => 'company_id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if ($model->company) {
            if (Yii::$app->user->can('app_company-view', ['route' => true])) {
                return Html::a($model->company->name, ['//company/view', 'id' => $model->company->id,]);
            }
            return $model->company->name;
        }
        return '';
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'product_type_id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if ($model->productType) {
            if (Yii::$app->user->can('app_product-type-view', ['route' => true])) {
                return Html::a($model->productType->name, ['//product-type/view', 'id' => $model->productType->id,]);
            }
            return $model->productType->name;
        }
        return '';
    },
    'format' => 'raw',
    //'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'item_type_id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if ($model->itemType) {
            if (Yii::$app->user->can('app_item-type-view', ['route' => true])) {
                return Html::a($model->itemType->name, ['//item-type/view', 'id' => $model->itemType->id,]);
            }
            return $model->itemType->name;
        }
        return '';
    },
    'format' => 'raw',
    //'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'option_id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if ($model->option) {
            if (Yii::$app->user->can('app_option-view', ['route' => true])) {
                return Html::a($model->option->name, ['//option/view', 'id' => $model->option->id,]);
            }
            return $model->option->name;
        }
        return '';
    },
    'format' => 'raw',
    //'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'component_id',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        if ($model->component) {
            if (Yii::$app->user->can('app_component-view', ['route' => true])) {
                return Html::a($model->component->code . ' ' . $model->component->name, ['//component/view', 'id' => $model->component->id,]);
            }
            return $model->component->code . ' ' . $model->component->name;
        }
        return '';
    },
    'format' => 'raw',
];
$columns[] = 'size';
$columns[] = [
    'label' => 'Options',
    'value' => function ($model) {
        /** @var CompanyRate $model */
        return $model->getCompanyRateOptionsHtml();
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'price',
    'hAlign' => 'right',
];

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#company-rate-searchModal',
]);
if (Yii::$app->user->can('app_company-rate_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    //'multiActions' => $multiActions,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('app', 'Company Rates'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);
