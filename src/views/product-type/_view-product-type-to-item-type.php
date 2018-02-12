<?php

use app\components\fields\BaseField;
use app\components\quotes\items\BaseItemQuote;
use app\models\ProductType;
use app\models\ProductTypeToItemType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;
use yii\web\View;
use app\components\ReturnUrl;

/**
 * @var View $this
 * @var ProductType $productType
 * @var ProductTypeToItemType $productTypeToItemType
 */

?>
<div class="box box-primary box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $productTypeToItemType->name ?></h3>
        <span class="label label-default"><?= $productTypeToItemType->itemType->name ?></span>
        <span class="label label-default">x<?= $productTypeToItemType->quantity ?></span>
        <span class="label label-info"><?= BaseItemQuote::opts()[$productTypeToItemType->quote_class] ?></span>
        <div class="box-tools pull-right">
            
            <?= Html::a('<i class="fa fa-pencil"></i>', ['/product-type-to-item-type/update', 'id' => $productTypeToItemType->id, 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-box-tool']) ?>
            <?= Html::a('<i class="fa fa-copy"></i>', ['/product-type-to-item-type/copy', 'id' => $productTypeToItemType->id], ['class' => 'btn btn-box-tool']) ?>
            <?= Html::a('<i class="fa fa-trash"></i>', ['/product-type-to-item-type/delete', 'id' => $productTypeToItemType->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'btn btn-box-tool',
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fa fa-arrows sortable-handle sortable-handle-item"></i>') ?>
        </div>
    </div>
    <div class="box-body">

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Options'); ?></h3>
                <div class="box-tools pull-right">
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Option'), [
                        '//product-type-to-option/create',
                        'ProductTypeToOption' => ['product_type_id' => $productType->id, 'product_type_to_item_type_id' => $productTypeToItemType->id],
                        'ru' => ReturnUrl::getToken()
                    ], ['class' => 'btn btn-box-tool']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php
                $sortableOptions = [];
                foreach ($productTypeToItemType->productTypeToOptions as $productTypeToOption) {
                    $sortableOptions[] = [
                        'content' => $this->render('_view-product-type-to-option', [
                            'productType' => $productType,
                            'productTypeToOption' => $productTypeToOption,
                        ]),
                        'options' => [
                            'id' => 'ProductTypeToOption_' . $productTypeToOption->id,
                            'class' => 'list-group-item',
                            'style' => 'border:0;',
                        ],
                    ];
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
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Components'); ?></h3>
                <div class="box-tools pull-right">
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Component'), [
                        '//product-type-to-component/create',
                        'ProductTypeToComponent' => ['product_type_id' => $productType->id, 'product_type_to_item_type_id' => $productTypeToItemType->id],
                        'ru' => ReturnUrl::getToken()
                    ], ['class' => 'btn btn-box-tool']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php
                $sortableComponents = [];
                foreach ($productTypeToItemType->productTypeToComponents as $productTypeToComponent) {
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

    </div>
</div>





