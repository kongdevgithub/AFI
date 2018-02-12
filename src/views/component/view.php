<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ProductType;
use app\models\ProductTypeToOption;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Component $model
 */

$this->title = Yii::t('app', 'Component') . ' ' . $model->name . ' (' . $model->code . ')';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="component-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Component'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            /** @var BaseComponentQuote $quote */
            $quote = new $model->quote_class;
            $attributes = [];
            $attributes[] = 'code';
            $attributes[] = 'name';
            $attributes[] = 'brand';
            $attributes[] = [
                'attribute' => 'component_type_id',
                'value' => Html::a($model->componentType->name, ['/component-type/view', 'id' => $model->componentType->id, 'ru' => ReturnUrl::getToken()]),
                'format' => 'raw',
            ];
            $attributes[] = [
                'attribute' => 'quote_class',
                'value' => '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>',
                'format' => 'raw',
            ];
            $attributes[] = 'make_ready_cost';
            if (Yii::$app->user->can('_view_cost_prices')) {
                $attributes[] = 'unit_cost';
                $attributes[] = 'minimum_cost';
                $attributes[] = 'quantity_factor';
            }
            $attributes[] = 'unit_weight';
            $attributes[] = 'unit_dead_weight';
            $attributes[] = 'unit_cubic_weight';
            $attributes[] = 'track_stock';
            $attributes[] = 'quality_check';
            $attributes[] = 'quality_code';
            $attributes[] = [
                'attribute' => 'component_config',
                'value' => '<pre>' . $model->component_config . '</pre>',
                'format' => 'raw',
            ];

            if ($model->dearProduct) {
                $attributes[] = [
                    'label' => Yii::t('app', 'Dear ID'),
                    'value' => $model->dearProduct->dear_id,
                ];
            }

            echo DetailView::widget([
                'model' => $model,
                'attributes' => $attributes,
            ]);
            ?>
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
                foreach (ProductTypeToOption::find()->notDeleted()->all() as $productTypeToOption) {
                    $values = $productTypeToOption->getValuesDecoded();
                    if ($values) {
                        foreach ($values as $value) {
                            if ($value == $model->id) {
                                $productTypes[strip_tags($productTypeToOption->productType->getBreadcrumbHtml('.'))] = $productTypeToOption->productType;
                            }
                        }
                    }
                }
                foreach ($model->productTypeToComponents as $productTypeToComponent) {
                    $productTypes[strip_tags($productTypeToComponent->productType->getBreadcrumbHtml('.'))] = $productTypeToComponent->productType;
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
