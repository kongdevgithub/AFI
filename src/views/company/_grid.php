<?php

use app\components\GridView;
use app\models\AccountTerm;
use app\models\Company;
use app\models\Industry;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\User;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CompanySearch $searchModel
 */


$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Company $model */
        return Yii::$app->user->can('app_company_view') ? Html::a($model->id, ['/company/view', 'id' => $model->id]) : $model->id;
    },
    'format' => 'raw',
];
$columns[] = 'name';
$columns[] = 'website';
$columns[] = [
    'attribute' => 'staff_rep_id',
    'value' => function ($model) {
        /** @var Company $model */
        return $model->staffRep ? $model->staffRep->label : null;
    },
    'filter' => ArrayHelper::map(User::find()->orderBy(['username' => SORT_ASC])->all(), 'id', 'label'),
];
$columns[] = [
    'attribute' => 'price_structure_id',
    'value' => function ($model) {
        /** @var Company $model */
        return $model->priceStructure ? $model->priceStructure->name : null;
    },
    'filter' => ArrayHelper::map(PriceStructure::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
];
$columns[] = [
    'attribute' => 'account_term_id',
    'value' => function ($model) {
        /** @var Company $model */
        return $model->accountTerm ? $model->accountTerm->name : null;
    },
    'filter' => ArrayHelper::map(AccountTerm::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
];
$columns[] = [
    'attribute' => 'job_type_id',
    'value' => function ($model) {
        /** @var Company $model */
        return $model->jobType ? $model->jobType->name : null;
    },
    'filter' => ArrayHelper::map(JobType::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
];
$columns[] = [
    'attribute' => 'industry_id',
    'value' => function ($model) {
        /** @var Company $model */
        return $model->industry ? $model->industry->name : null;
    },
    'filter' => ArrayHelper::map(Industry::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Company $model */
        return Yii::$app->user->can('app_company_status') ? $model->getStatusButton() : $model->getWorkflowStatus()->getLabel();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'contentOptions' => ['class' => 'text-center'],
    'format' => 'html',
];
//$columns[] = [
//    'attribute' => 'default_contact_id',
//    'value' => function ($model) {
//        /** @var Company $model */
//        return $model->defaultContact ? $model->defaultContact->label : null;
//    },
//];


$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#package-searchModal',
]);
if (Yii::$app->user->can('app_company_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
    $gridActions[] = Html::a('<i class="fa fa-link"></i> ' . Yii::t('app', 'Create in HubSpot'),
        'https://app.hubspot.com/sales/2659477/companies/list/view/all/',
        ['class' => 'btn btn-default btn-xs', 'target' => '_blank']);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'gridActions' => $gridActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Companies'),
    ],
]);

