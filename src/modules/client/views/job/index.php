<?php

use app\models\Company;
use app\models\Job;
use app\models\User;
use kartik\select2\Select2;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\ReturnUrl;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\JobSearch $searchModel
 */

$this->title = Yii::t('app', 'Jobs');
//$this->params['breadcrumbs'][] = $this->title;

$users = ArrayHelper::map(User::find()->orderBy('username')->all(), 'id', 'label');

$columns = [];
$columns[] = [
    'attribute' => 'vid',
    'value' => function ($model) {
        /** @var Job $model */
        $items = [];
        $items[] = ['label' => Yii::t('app', 'View'), 'url' => ['//client/job/view', 'id' => $model->id]];
        $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//client/job/update', 'id' => $model->id]];
        //$items[] = ['label' => Yii::t('app', 'Version'), 'url' => ['//client/job/version', 'id' => $model->id]];
        //$items[] = ['label' => Yii::t('app', 'Copy'), 'url' => ['//client/job/copy', 'id' => $model->id]];
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//client/job/view', 'id' => $model->id]),
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
        return Html::a($model->name, ['//client/job/preview', 'id' => $model->id], ['class' => 'modal-remote']);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'company_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->company ? $model->company->name : '';
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
            //'multiple' => true,
            'ajax' => [
                'url' => Url::to(['company/json-list']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
        ],
    ]),
    'contentOptions' => ['style' => 'width:200px;'],
];
$columns[] = [
    'attribute' => 'contact_id',
    'value' => function ($model) {
        /** @var Job $model */
        if (!$model->contact) {
            return '';
        }
        return $model->contact->getLabel(true);
    },
    'format' => 'raw',
    'enableSorting' => false,
//    'filter' => Select2::widget([
//        'name' => 'JobSearch[contact_id]',
//        'value' => $searchModel->contact_id,
//        //'data' => ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name'),
//        'options' => ['placeholder' => ''],
//        'pluginOptions' => [
//            'allowClear' => true,
//            'ajax' => [
//                'url' => Url::to(['contact/json-list']),
//                'dataType' => 'json',
//                'data' => new JsExpression('function(params) { return {q:params.term}; }')
//            ],
//        ],
//    ]),
    //'contentOptions' => ['style' => 'width:200px;'],
];
$columns[] = [
    'attribute' => 'staff_rep_id',
    'value' => function ($model) {
        /** @var Job $model */
        return $model->staffRep ? $model->staffRep->getLabel(true) : null;
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
//$columns[] = [
//    'attribute' => 'production_at',
//    'format' => 'date',
//    'contentOptions' => ['class' => 'text-center'],
//];
//$columns[] = [
//    'attribute' => 'packed_at',
//    'format' => 'date',
//    'contentOptions' => ['class' => 'text-center'],
//];
//$columns[] = [
//    'attribute' => 'complete_at',
//    'format' => 'date',
//    'contentOptions' => ['class' => 'text-center'],
//];
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

?>

<div class="job-index">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Jobs'); ?></h3>
            <div class="box-tools pull-right">
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Job'), [
                    'create',
                    'ru' => ReturnUrl::getToken()
                ], ['class' => 'btn btn-box-tool']) ?>
                <?= Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search Jobs'), [
                    'class' => 'btn btn-box-tool',
                    'data-toggle' => 'modal',
                    'data-target' => '#job-searchModal',
                ]) ?>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'layout' => '{summary}{pager}{items}{pager}',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => $columns,
                ]); ?>
            </div>
        </div>
    </div>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

</div>