<?php

use app\components\GridView;
use app\models\Contact;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ContactSearch $searchModel
 */

$columns = [
    [
        'attribute' => 'id',
        'value' => function ($model) {
            /** @var Contact $model */
            return Yii::$app->user->can('app_contact_view') ? Html::a($model->id, ['/contact/view', 'id' => $model->id]) : $model->id;
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'default_company_id',
        'value' => function ($model) {
            /** @var Contact $model */
            return $model->defaultCompany ? Html::a($model->defaultCompany->name, ['//company/view', 'id' => $model->defaultCompany->id]) : '';
        },
        'format' => 'raw',
        'enableSorting' => false,
        'filter' => Select2::widget([
            'name' => 'ContactSearch[default_company_id]',
            'value' => $searchModel->default_company_id,
            //'data' => ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name'),
            'options' => ['placeholder' => ''],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => Url::to(['company/json-list']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
            ],
        ]),
        'contentOptions' => ['style' => 'width:200px;'],
    ],
    [
        'label' => false,
        'attribute' => 'avatar',
        'contentOptions' => ['class' => 'text-center'],
        'format' => 'raw',
    ],
    'first_name',
    'last_name',
    'status',
    'phone',
    'email:email',
];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#package-searchModal',
]);
if (Yii::$app->user->can('app_contact_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
    $gridActions[] = Html::a('<i class="fa fa-link"></i> ' . Yii::t('app', 'Create in HubSpot'),
        'https://app.hubspot.com/sales/2659477/contacts/list/view/all/',
        ['class' => 'btn btn-default btn-xs', 'target' => '_blank']);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Contacts'),
    ],
]);
