<?php

use app\components\fields\BaseField;
use app\components\quotes\products\BaseProductQuote;
use app\models\ProductType;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var View $this
 * @var ProductType $model
 */

$this->title = Yii::t('app', 'Product Type') . ' ' . $model->name;
$this->params['heading'] = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['index']];
if ($model->parent) {
    foreach ($model->parent->getBreadcrumb() as $breadcrumb) {
        $this->params['breadcrumbs'][] = ['label' => $breadcrumb->name, 'url' => ['view', 'id' => $breadcrumb->id]];
    }
}
$this->params['breadcrumbs'][] = ['label' => $this->params['heading'], 'url' => '#'];
?>
<div class="product-type-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Product Type'); ?></h3>
                    <div class="box-tools pull-right">
                        <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'), ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-box-tool']) ?>
                        <?= Html::a('<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'), ['copy', 'id' => $model->id], ['class' => 'btn btn-box-tool']) ?>
                        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-box-tool',
                            'data-confirm' => Yii::t('app', 'Are you sure?'),
                            'data-method' => 'post',
                        ]); ?>
                    </div>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'name',
                            [
                                'attribute' => 'quote_class',
                                'value' => $model->quote_class ? '<span class="label label-info">' . BaseProductQuote::opts()[$model->quote_class] . '</span>' : '',
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'image',
                                'value' => Html::img($model->getImageSrc(), ['width' => 200]),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'config',
                                'value' => $model->config ? '<pre>' . Json::encode($model->getConfigDecoded(), JSON_PRETTY_PRINT) . '</pre>' : '',
                                'format' => 'raw',
                            ],
                            //[
                            //    'format' => 'html',
                            //    'attribute' => 'parent_id',
                            //    'value' => $model->parent ? $model->parent->getBreadcrumbHtml() : '',
                            //],
                        ],
                    ]); ?>
                </div>
            </div>

            <?php
            if (!$model->productTypes) {
                ?>
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Global Options'); ?></h3>
                        <div class="box-tools pull-right">
                            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Option'), [
                                '//product-type-to-option/create',
                                'ProductTypeToOption' => ['product_type_id' => $model->id],
                                'ru' => ReturnUrl::getToken()
                            ], ['class' => 'btn btn-box-tool']) ?>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <?php
                        $sortableOptions = [];
                        foreach ($model->productTypeToOptions as $productTypeToOption) {
                            if (!$productTypeToOption->product_type_to_item_type_id) {
                                $sortableOptions[] = [
                                    'content' => $this->render('_view-product-type-to-option', [
                                        'productType' => $model,
                                        'productTypeToOption' => $productTypeToOption,
                                    ]),
                                    'options' => [
                                        'id' => 'ProductTypeToOption_' . $productTypeToOption->id,
                                        'class' => 'list-group-item',
                                        'style' => 'border:0;',
                                    ],
                                ];
                            }
                        }
                        echo Sortable::widget([
                            'items' => $sortableOptions,
                            'options' => [
                                'class' => 'list-group',
                                'style' => 'margin-bottom:0;',
                            ],
                            'clientOptions' => [
                                'cursor' => 'move',
                                'handle' => '.sortable-handle-option',
                                'update' => new JsExpression("function(event, ui){
                                    $.ajax({
                                        type: 'POST',
                                        url: '" . Url::to(['/product-type-to-option/sort']) . "',
                                        data: $(event.target).sortable('serialize')
                                    });
                                }"),
                            ],
                        ]);
                        ?>

                        <?php /* ?>
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= Yii::t('app', 'Components'); ?></h3>
                                <div class="box-tools pull-right">
                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Component'), [
                                        '//product-type-to-component/create',
                                        'ProductTypeToComponent' => ['product_type_id' => $model->id],
                                        'ru' => ReturnUrl::getToken()
                                    ], ['class' => 'btn btn-box-tool']) ?>
                                </div>
                            </div>
                            <div class="box-body no-padding">
                                <?php
                                $sortableComponents = [];
                                foreach ($model->productTypeToComponents as $productTypeToComponent) {
                                    if (!$productTypeToComponent->product_type_to_item_type_id) {
                                        $sortableComponents[] = [
                                            'content' => $this->render('_view-product-type-to-component', [
                                                'productType' => $productType,
                                                'productTypeToComponent' => $productTypeToComponent,
                                            ]),
                                            'options' => [
                                                'id' => 'ProductTypeToComponent_' . $productTypeToComponent->id,
                                                'class' => 'list-group-item',
                                                'style' => 'border:0;',
                                            ],
                                        ];
                                    }
                                }
                                echo Sortable::widget([
                                    'items' => $sortableComponents,
                                    'options' => [
                                        'class' => 'list-group',
                                        'style' => 'margin-bottom:0;',
                                    ],
                                    'clientOptions' => [
                                        'cursor' => 'move',
                                        'handle' => '.sortable-handle-option',
                                        'update' => new JsExpression("function(event, ui){
                                        $.ajax({
                                            type: 'POST',
                                            url: '" . Url::to(['/product-type-to-component/sort']) . "',
                                            data: $(event.target).sortable('serialize')
                                        });
                                    }"),
                                    ],
                                ]);
                                ?>
                            </div>
                        </div>
                        <?php */ ?>
                    </div>
                </div>
                <?php
            }
            ?>

        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <?= $this->render('/note/_index', ['model' => $model]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->render('/attachment/_index', ['model' => $model]) ?>
                </div>
            </div>
         </div>
    </div>

    <?php
    if (!$model->productTypes) {
        ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Items'); ?></h3>
                <div class="box-tools pull-right">
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Item'), [
                        '//product-type-to-item-type/create',
                        'ProductTypeToItemType' => ['product_type_id' => $model->id],
                        'ru' => ReturnUrl::getToken(),
                    ], ['class' => 'btn btn-box-tool']) ?>
                </div>
            </div>
            <div class="box-body">
                <?php
                $sortableItems = [];
                foreach ($model->productTypeToItemTypes as $productTypeToItemType) {
                    $sortableItems[] = [
                        'content' => $this->render('_view-product-type-to-item-type', [
                            'productType' => $model,
                            'productTypeToItemType' => $productTypeToItemType,
                        ]),
                        'options' => [
                            'id' => 'ProductTypeToItemType_' . $productTypeToItemType->id,
                            'class' => 'col-md-4',
                            'tag' => 'div',
                        ],
                    ];
                }
                echo Sortable::widget([
                    'items' => $sortableItems,
                    'options' => [
                        'tag' => 'div',
                        'class' => 'row row-md-4-clear',
                        'style' => 'margin-bottom:0;',
                    ],
                    'clientOptions' => [
                        'cursor' => 'move',
                        'handle' => '.sortable-handle-item',
                        'update' => new JsExpression("function(event, ui){
                                $.ajax({
                                    type: 'POST',
                                    url: '" . Url::to(['/product-type-to-item-type/sort']) . "',
                                    data: $(event.target).sortable('serialize')
                                });
                            }"),
                    ],
                ]);
                ?>
            </div>
        </div>
        <?php
    }
    ?>

    <?php
    if (!$model->productTypeToItemTypes && !$model->productTypeToOptions && !$model->productTypeToComponents) {
        ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Children'); ?></h3>
                <div class="box-tools pull-right">
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Child'), [
                        'create',
                        'ProductType' => ['parent_id' => $model->id],
                        'ru' => ReturnUrl::getToken()
                    ], ['class' => 'btn btn-box-tool']) ?>
                </div>
            </div>
            <div class="box-body">
                <?php
                $sortableChildren = [];
                foreach ($model->productTypes as $child) {
                    $sortableChildren[] = [
                        'content' => $this->render('_view-product-type', [
                            'productType' => $child,
                        ]),
                        'options' => [
                            'id' => 'ProductType_' . $child->id,
                        ],
                    ];
                }
                echo Sortable::widget([
                    'items' => $sortableChildren,
                    'options' => [
                        'class' => 'list-inline',
                        'style' => 'margin-bottom:0;',
                    ],
                    'itemOptions' => [
                        'class' => 'text-center',
                    ],
                    'clientOptions' => [
                        'cursor' => 'move',
                        'handle' => '.sortable-handle-child',
                        'update' => new JsExpression("function(event, ui){
                        $.ajax({
                            type: 'POST',
                            url: '" . Url::to(['/product-type/sort']) . "',
                            data: $(event.target).sortable('serialize')
                        });
                    }"),
                    ],
                ]);
                //$items = [];
                //foreach ($model->productTypes as $child) {
                //    $items[] = [
                //        'label' => Html::img($child->getImageSrc(), ['width' => '150', 'height' => '150']) . '<br>' . $child->name,
                //        'url' => ['/product-type/view', 'id' => $child->id, 'ru' => ReturnUrl::getToken()],
                //        'encode' => false,
                //    ];
                //}
                //echo Nav::widget([
                //    'items' => $items,
                //    'options' => ['class' => 'nav-pills', 'style' => 'text-align:center;'],
                //]);
                ?>
            </div>
        </div>
        <?php
    }
    ?>

</div>
