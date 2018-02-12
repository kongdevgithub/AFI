<?php

use app\components\fields\BaseField;
use app\models\ProductType;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Option $model
 */

$this->title = Yii::t('app', 'Option') . ' ' . $model->name;
$this->params['heading'] = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="option-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Option'); ?></h3>
            <div class="box-tools pull-right">
                <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'), ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-box-tool']) ?>
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
                        'attribute' => 'field_class',
                        'value' => $model->field_class ? BaseField::opts()[$model->field_class] : null,
                    ],
                    [
                        'attribute' => 'field_config',
                        'value' => '<pre>' . VarDumper::export(Json::decode($model->field_config)) . '</pre>',
                        'format' => 'html',
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Product Types'); ?></h3>
        </div>
        <div class="box-body">

            <div class="table-responsive">
                <?php
                $productTypes = [];
                foreach ($model->productTypeToOptions as $productTypeToOption) {
                    $productTypes[strip_tags($productTypeToOption->productType->getBreadcrumbHtml('.'))] = $productTypeToOption->productType;
                }
                ksort($productTypes);
                echo GridView::widget([
                    'layout' => '{summary}{pager}{items}{pager}',
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $productTypes,
                        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-components'],
                    ]),
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'value' => function ($model) {
                                /** @var ProductType $model */
                                return $model->getBreadcrumbHtml();
                            },
                            'format' => 'raw',
                        ],
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>

</div>
