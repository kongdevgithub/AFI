<?php

use app\components\GridView;
use app\components\ReturnUrl;
use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Sport;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use kartik\select2\Select2;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\ProductSearch $searchModel
 */
$columns = [];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->getStatusButton();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'headerOptions' => [
        'style' => 'width:50px;',
    ],
    'format' => 'raw',
    'enableSorting' => false,
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Product $model */
        return Yii::$app->user->can('goldoc_product_view') ? Html::a($model->id, ['product/view', 'id' => $model->id]) : $model->id;
    },
    'format' => 'raw',
    'headerOptions' => [
        'style' => 'width:60px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'loc',
    'headerOptions' => [
        'style' => 'width:75px;',
    ],
];
$columns[] = [
    'attribute' => 'item_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->item ? Html::a($model->item->code, ['item/view', 'id' => $model->item->id], [
            'title' => $model->item->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[item_id]',
        'value' => $searchModel->item_id,
        'data' => ArrayHelper::map(Item::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'colour_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->colour ? Html::a($model->colour->code, ['colour/view', 'id' => $model->colour->id], [
            'title' => $model->colour->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[colour_id]',
        'value' => $searchModel->colour_id,
        'data' => ArrayHelper::map(Colour::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'design_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->design ? Html::a($model->design->code, ['design/view', 'id' => $model->design->id], [
            'title' => $model->design->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[design_id]',
        'value' => $searchModel->design_id,
        'data' => ArrayHelper::map(Design::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'substrate_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->substrate ? Html::a($model->substrate->code, ['substrate/view', 'id' => $model->substrate->id], [
            'title' => $model->substrate->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[substrate_id]',
        'value' => $searchModel->substrate_id,
        'data' => ArrayHelper::map(Substrate::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'sizeName',
];
$columns[] = [
    'attribute' => 'quantity',
    'headerOptions' => [
        'style' => 'width:50px;',
    ],
];
$columns[] = [
    'attribute' => 'goldoc_manager_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->goldocManager ? Html::a($model->goldocManager->initials, ['//user/profile/show', 'id' => $model->goldocManager->id], [
            'class' => 'modal-remote',
            'title' => $model->goldocManager->label,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[goldoc_manager_id]',
        'value' => $searchModel->goldoc_manager_id,
        'data' => ArrayHelper::map(User::find()
            ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc')])
            ->orderBy('username')->all(), 'id', 'initials'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:60px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'active_manager_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->activeManager ? Html::a($model->activeManager->initials, ['//user/profile/show', 'id' => $model->activeManager->id], [
            'class' => 'modal-remote',
            'title' => $model->activeManager->label,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[active_manager_id]',
        'value' => $searchModel->active_manager_id,
        'data' => ArrayHelper::map(User::find()
            ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-active')])
            ->orderBy('username')->all(), 'id', 'initials'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:60px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'sport_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->sport ? Html::a($model->sport->code, ['sport/view', 'id' => $model->sport->id], [
            'title' => $model->sport->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[sport_id]',
        'value' => $searchModel->sport_id,
        'data' => ArrayHelper::map(Sport::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'supplier_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->supplier ? Html::a($model->supplier->code, ['supplier/view', 'id' => $model->supplier->id], [
            'title' => $model->supplier->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'enableSorting' => false,
    'filter' => Select2::widget([
        'name' => 'ProductSearch[supplier_id]',
        'value' => $searchModel->supplier_id,
        'data' => ArrayHelper::map(Supplier::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['placeholder' => ''],
        'pluginOptions' => ['allowClear' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'product_price',
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'labour_price',
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'machine_price',
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'total_price',
    'hAlign' => 'right',
];

$gridActions = [];
if (Yii::$app->user->can('goldoc_product_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('goldoc', 'Create'), [
        'product/create',
        'Product' => ['venue_id' => $searchModel->venue_id],
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('goldoc', 'Products'),
    ],
]);
