<?php

use app\models\ProductType;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ProductTypeSearch $searchModel
 */

$this->title = Yii::t('app', 'Product Types');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-type-index">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Product Types'); ?></h3>
            <div class="box-tools pull-right">
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Product Type'), [
                    'create',
                    'ru' => ReturnUrl::getToken()
                ], ['class' => 'btn btn-box-tool']) ?>
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Permissions'), [
                    'permissions',
                ], ['class' => 'btn btn-box-tool']) ?>
            </div>
        </div>
        <div class="box-body">
            <?php
            $sortableChildren = [];
            foreach ($dataProvider->getModels() as $productType) {
                $sortableChildren[] = [
                    'content' => $this->render('_view-product-type', [
                        'productType' => $productType,
                    ]),
                    'options' => [
                        'id' => 'ProductType_' . $productType->id,
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
            //foreach ($dataProvider->getModels() as $productType) {
            //    /** @var ProductType $productType */
            //    $items[] = [
            //        'label' => Html::img($productType->getImageSrc(), ['width' => '150', 'height' => '150']) . '<br>' . $productType->name,
            //        'url' => ['/product-type/view', 'id' => $productType->id, 'ru' => ReturnUrl::getToken()],
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

</div>