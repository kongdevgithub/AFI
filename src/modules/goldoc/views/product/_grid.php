<?php

use app\components\GridView;
use app\components\ReturnUrl;
use app\models\Search;
use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Installer;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Sponsor;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\Type;
use app\modules\goldoc\models\Venue;
use kartik\grid\CheckboxColumn;
use kartik\select2\Select2;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\ProductSearch $searchModel
 */

$columns = [];
$columns[] = [
    'class' => CheckboxColumn::className(),
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
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Product $model */
        $afiStatus = $model->getAfiStatusButtons(true);
        if ($afiStatus) {
            $afiStatus = '<hr style="margin:2px 0">' . $afiStatus;
        }
        return $model->getStatusButton() . $afiStatus;
    },
    'filter' => Select2::widget([
        'name' => 'ProductSearch[status]',
        'value' => $searchModel->status,
        'data' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:50px;',
    ],
    'format' => 'raw',
    'enableSorting' => false,
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'artwork',
    'value' => function ($model) {
        /** @var Product $model */
        if ($model->artwork) {
            $thumb = Html::img($model->artwork->getFileUrl('100x100'), ['style' => 'max-height:50px;max-width:50px;']);
            if (Yii::$app->user->can('goldoc_product_artwork', ['route' => true])) {
                return Html::a($thumb, $model->getUrl('artwork', ['ru' => ReturnUrl::getToken()]), ['class' => 'modal-remote']);
            }
            return Html::a($thumb, $model->artwork->getFileUrl('800x800'), ['data-fancybox' => 'gallery-' . $model->artwork->id]);
        }
        $thumb = '<i class="fa fa-upload" style="font-size:50px;line-height:50px;"></i>';
        if (Yii::$app->user->can('goldoc_product_artwork', ['route' => true])) {
            return Html::a($thumb, ['product/artwork', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote',
                //'title' => Yii::t('goldoc', 'Artwork'),
                //'data-toggle' => 'tooltip',
            ]);
        }
        return $thumb;
    },
    'headerOptions' => [
        'style' => 'width:100px;',
    ],
    'format' => 'raw',
    'enableSorting' => false,
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'venue_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->venue ? Html::a($model->venue->code, ['venue/view', 'id' => $model->venue->id], [
            'title' => $model->venue->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'filter' => Select2::widget([
        'name' => 'ProductSearch[venue_id]',
        'value' => $searchModel->venue_id,
        'data' => ArrayHelper::map(Venue::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
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
    'attribute' => 'type_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->type ? Html::a($model->type->code, ['type/view', 'id' => $model->type->id], [
            'title' => $model->type->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'filter' => Select2::widget([
        'name' => 'ProductSearch[type_id]',
        'value' => $searchModel->type_id,
        'data' => ArrayHelper::map(Type::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
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
    'filter' => Select2::widget([
        'name' => 'ProductSearch[item_id]',
        'value' => $searchModel->item_id,
        'data' => ArrayHelper::map(Item::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
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
    'filter' => Select2::widget([
        'name' => 'ProductSearch[colour_id]',
        'value' => $searchModel->colour_id,
        'data' => ArrayHelper::map(Colour::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
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
    'filter' => Select2::widget([
        'name' => 'ProductSearch[design_id]',
        'value' => $searchModel->design_id,
        'data' => ArrayHelper::map(Design::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
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
    'filter' => Select2::widget([
        'name' => 'ProductSearch[substrate_id]',
        'value' => $searchModel->substrate_id,
        'data' => ArrayHelper::map(Substrate::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
$columns[] = [
    'attribute' => 'name',
];
$columns[] = [
    'attribute' => 'details',
];
$columns[] = [
    'attribute' => 'width',
    'headerOptions' => [
        'style' => 'width:50px',
    ],
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'height',
    'headerOptions' => [
        'style' => 'width:50px',
    ],
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'depth',
    'headerOptions' => [
        'style' => 'width:50px',
    ],
    'hAlign' => 'right',
];
$columns[] = [
    'attribute' => 'quantity',
    'headerOptions' => [
        'style' => 'width:50px;',
    ],
    'hAlign' => 'right',
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
        'options' => ['multiple' => true],
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
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:60px',
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
    'filter' => Select2::widget([
        'name' => 'ProductSearch[supplier_id]',
        'value' => $searchModel->supplier_id,
        'data' => ArrayHelper::map(Supplier::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
//$columns[] = [
//    'attribute' => 'sponsor_id',
//    'value' => function ($model) {
//        /** @var Product $model */
//        return $model->sponsor ? Html::a($model->sponsor->code, ['sponsor/view', 'id' => $model->sponsor->id], [
//            'title' => $model->sponsor->name,
//            'data-toggle' => 'tooltip',
//        ]) : '';
//    },
//    'format' => 'raw',
//    'filter' => Select2::widget([
//        'name' => 'ProductSearch[sponsor_id]',
//        'value' => $searchModel->sponsor_id,
//        'data' => ArrayHelper::map(Sponsor::find()->orderBy('code')->all(), 'id', 'code'),
//        'options' => ['multiple' => true],
//    ]),
//    'headerOptions' => [
//        'style' => 'width:75px',
//    ],
//    'hAlign' => 'center',
//];
$columns[] = [
    'attribute' => 'installer_id',
    'value' => function ($model) {
        /** @var Product $model */
        return $model->installer ? Html::a($model->installer->code, ['installer/view', 'id' => $model->installer->id], [
            'title' => $model->installer->name,
            'data-toggle' => 'tooltip',
        ]) : '';
    },
    'format' => 'raw',
    'filter' => Select2::widget([
        'name' => 'ProductSearch[installer_id]',
        'value' => $searchModel->installer_id,
        'data' => ArrayHelper::map(Installer::find()->orderBy('code')->all(), 'id', 'code'),
        'options' => ['multiple' => true],
    ]),
    'headerOptions' => [
        'style' => 'width:75px',
    ],
    'hAlign' => 'center',
];
if (Yii::$app->user->can('_goldoc_view_prices')) {
    $columns[] = [
        'attribute' => 'product_price',
        'headerOptions' => [
            'style' => 'width:50px;',
        ],
        'hAlign' => 'right',
    ];
    $columns[] = [
        'attribute' => 'labour_price',
        'headerOptions' => [
            'style' => 'width:50px;',
        ],
        'hAlign' => 'right',
    ];
    $columns[] = [
        'attribute' => 'machine_price',
        'headerOptions' => [
            'style' => 'width:50px;',
        ],
        'hAlign' => 'right',
    ];
    $columns[] = [
        'attribute' => 'total_price',
        'headerOptions' => [
            'style' => 'width:50px;',
        ],
        'hAlign' => 'right',
    ];
}

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('goldoc', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#product-searchModal',
]);
if (Yii::$app->user->can('goldoc_product_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('goldoc', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}
if (Yii::$app->user->can('goldoc_product_export', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-download"></i> ' . Yii::t('goldoc', 'Export'), [
        'product/export',
        'ProductSearch' => Yii::$app->request->get('ProductSearch'),
        'ru' => ReturnUrl::getToken(),
    ], ['class' => 'btn btn-default btn-xs modal-remote']);
}
if (Yii::$app->user->can('goldoc_product_save-search', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-save"></i> ' . Yii::t('goldoc', 'Save Search'), [
        'product/save-search',
        'ProductSearch' => Yii::$app->request->get('ProductSearch'),
        'ru' => ReturnUrl::getToken(),
    ], ['class' => 'btn btn-default btn-xs modal-remote']);
    foreach (Search::find()->andWhere(['user_id' => Yii::$app->user->id, 'model_name' => $searchModel->className()])->all() as $search) {
        $gridActions[] = ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to([
                    'product/index',
                    'ProductSearch' => Json::decode($search->model_params),
                    'ru' => ReturnUrl::getToken(),
                ]),
                'class' => 'btn btn-default btn-xs',
            ],
            'label' => $search->name,
            'split' => true,
            'dropdown' => [
                'items' => [
                    [
                        'label' => Yii::t('goldoc', 'Remove') . ' ' . $search->name,
                        'url' => [
                            'product/save-search',
                            'delete' => $search->id,
                            'ru' => ReturnUrl::getToken(),
                        ],
                    ],
                ],
            ],
        ]);
    }
}

$multiActions = [
    [
        'label' => Yii::t('goldoc', 'Update'),
        'url' => ['product/bulk-update', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('goldoc', 'Artwork'),
        'url' => ['product/bulk-artwork', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('goldoc', 'Status'),
        'url' => ['product/bulk-status', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('goldoc', 'Delete'),
        'url' => ['product/bulk-delete', 'ru' => ReturnUrl::getToken()],
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    'multiActions' => $multiActions,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => Yii::t('goldoc', 'Products'),
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);
