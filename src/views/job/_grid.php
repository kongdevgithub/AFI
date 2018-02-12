<?php

use app\components\GridView;
use app\models\Company;
use app\models\Job;
use app\models\Search;
use app\models\User;
use kartik\select2\Select2;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use app\components\ReturnUrl;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\JobSearch $searchModel
 */

$users = ArrayHelper::map(User::find()->orderBy('username')->all(), 'id', 'label');

$columns = [];
$columns[] = [
    'attribute' => 'vid',
    'value' => function ($model) {
        /** @var Job $model */
        $items = [];
        if (Yii::$app->user->can('app_job_quote', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Quote'), 'url' => ['//job/quote', 'id' => $model->id]];
        }
        if (Yii::$app->user->can('app_job_finance', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Finance'), 'url' => ['//job/finance', 'id' => $model->id]];
        }
        if (Yii::$app->user->can('app_job_production', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Production'), 'url' => ['//job/production', 'id' => $model->id]];
        }
        if (Yii::$app->user->can('app_job_despatch', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Despatch'), 'url' => ['//job/despatch', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//job/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->vid,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'format' => 'raw',
    'enableSorting' => false,
    'headerOptions' => ['style' => 'width:120px;'],
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->getStatusButtons();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'name',
    'value' => function ($model) {
        /** @var Job $model */
        return Html::a($model->name, ['//job/preview', 'id' => $model->id], ['class' => 'modal-remote']);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'company_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->company ? Html::a($model->company->name, ['//company/preview', 'id' => $model->company->id], ['class' => 'modal-remote']) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'JobSearch[company_id]',
        'value' => $searchModel->company_id,
        'data' => $searchModel->company_id ? ArrayHelper::map(Company::find()->andWhere(['id' => $searchModel->company_id])->orderBy('name')->all(), 'id', 'name') : [],
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
    //'contentOptions' => ['style' => 'width:200px;'],
];
$columns[] = [
    'attribute' => 'contact_id',
    'value' => function ($model) {
        /** @var Job $model */
        if (!$model->contact) {
            return '';
        }
        return Html::a($model->contact->getLabel(true), ['//contact/preview', 'id' => $model->contact->id], ['class' => 'modal-remote']);
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'JobSearch[contact_id]',
        'value' => $searchModel->contact_id,
        //'data' => ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => [
            'allowClear' => true,
            'ajax' => [
                'url' => Url::to(['contact/json-list']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
        ],
    ]),
    //'contentOptions' => ['style' => 'width:200px;'],
];
$columns[] = [
    'label' => Yii::t('app', 'AM'),
    'attribute' => 'staff_rep_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->staffRep ? $model->staffRep->getLink() : null;
    },
    'filter' => $users,
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'label' => Yii::t('app', 'CSR'),
    'attribute' => 'staff_csr_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->staffCsr ? $model->staffCsr->getLink() : null;
    },
    'filter' => $users,
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'label' => Yii::t('app', 'DSN'),
    'attribute' => 'staff_designer_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->staffDesigner ? $model->staffDesigner->getLink() : null;
    },
    'filter' => $users,
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'label' => Yii::t('app', 'Counts'),
    'value' => function ($model) {
        /** @var Job $model */
        $productCount = $model->getProducts()->count();
        $itemCount = 0;
        $unitCount = 0;
        foreach ($model->products as $product) {
            $itemCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->count();
            $unitCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->sum('item.quantity') * $product->quantity;
        }
        return implode('&nbsp;|&nbsp;', [$productCount, $itemCount, $unitCount]);
    },
    'filter' => $users,
    'format' => 'raw',
    'enableSorting' => false,
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'label' => Yii::t('app', 'Size'),
    'value' => function ($model) {
        /** @var Job $model */
        $size = [];
        $area = $model->getArea();
        if ($area) {
            $size[] = ceil($area) . 'm<sup>2</sup>';
        }
        $perimeter = $model->getPerimeter();
        if ($perimeter) {
            $size[] = ceil($perimeter) . 'm';
        }
        return implode('&nbsp;|&nbsp;', $size);
    },
    'filter' => $users,
    'format' => 'raw',
    'enableSorting' => false,
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'attribute' => 'quote_at',
    'format' => 'date',
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'attribute' => 'production_at',
    'format' => 'date',
    'contentOptions' => ['class' => 'text-center'],
];
$columns[] = [
    'attribute' => 'complete_at',
    'format' => 'date',
    'contentOptions' => ['class' => 'text-center'],
];
//$columns[] = [
//    'attribute' => 'production_date',
//    'format' => 'date',
//    'contentOptions' => ['class' => 'text-center'],
//];
//$columns[] = [
//    'attribute' => 'despatch_date',
//    'format' => 'date',
//    'contentOptions' => ['class' => 'text-center'],
//];
$columns[] = [
    'attribute' => 'due_date',
    'format' => 'date',
    'contentOptions' => ['class' => 'text-center'],
];
if (Yii::$app->user->can('csr')) {
    $columns[] = [
        'attribute' => 'reportTotal',
        'contentOptions' => ['class' => 'text-right'],
    ];
}

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#job-searchModal',
]);
if (Yii::$app->user->can('app_job_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}
if (Yii::$app->user->can('app_job_export', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-download"></i> ' . Yii::t('app', 'Export'), ['job/export', 'JobSearch' => Yii::$app->request->get('JobSearch'), 'ru' => ReturnUrl::getToken()], [
        'title' => Yii::t('app', 'Export'),
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
}
if (Yii::$app->user->can('app_job_save-search', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-save"></i> ' . Yii::t('app', 'Save Search'), [
        'job/save-search',
        'JobSearch' => Yii::$app->request->get('JobSearch'),
        'ru' => ReturnUrl::getToken(),
    ], ['class' => 'btn btn-default btn-xs modal-remote']);
    foreach (Search::find()->andWhere(['user_id' => Yii::$app->user->id, 'model_name' => $searchModel->className()])->all() as $search) {
        $gridActions[] = ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to([
                    'job/index',
                    'JobSearch' => Json::decode($search->model_params),
                    'ru' => ReturnUrl::getToken(),
                ]),
                'class' => 'btn btn-default btn-xs',
            ],
            'label' => $search->name,
            'split' => true,
            'dropdown' => [
                'items' => [
                    [
                        'label' => Yii::t('app', 'Remove') . ' ' . $search->name,
                        'url' => [
                            'job/save-search',
                            'delete' => $search->id,
                            'ru' => ReturnUrl::getToken(),
                        ],
                    ],
                ],
            ],
        ]);
    }
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('app', 'Jobs'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);