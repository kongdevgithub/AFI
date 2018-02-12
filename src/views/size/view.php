<?php

use app\components\fields\BaseField;
use app\models\Option;
use app\models\ProductType;
use app\models\ProductTypeToOption;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Size $model
 */

$this->title = Yii::t('app', 'Size') . ' ' . $model->name;
$this->params['heading'] = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Sizes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="size-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Size'); ?></h3>
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
                    'id',
                    'name',
                    'width',
                    'height',
                    'depth',
                ],
            ]); ?>
        </div>
    </div>

    <?php
    $productTypes = [];
    $productTypeToOptions = ProductTypeToOption::find()
        ->notDeleted()
        ->andWhere(['option_id' => Option::OPTION_SIZE])
        ->all();
    foreach ($productTypeToOptions as $productTypeToOption) {
        $values = $productTypeToOption->getValuesDecoded();
        if ($values) {
            foreach ($values as $value) {
                if ($value == $model->id) {
                    $productTypes[strip_tags($productTypeToOption->productType->getBreadcrumbHtml('.'))] = $productTypeToOption->productType;
                }
            }
        }
    }
    if ($productTypes) {
        ksort($productTypes);
        ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Options'); ?></h3>
            </div>
            <div class="box-body">

                <div class="table-responsive">
                    <?php
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
        <?php
    }
    ?>

</div>
