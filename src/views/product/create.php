<?php

use app\models\ProductType;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\Job $job
 * @var app\models\ProductType $productType
 */


$this->title = $job->getTitle();

//if ($job) {
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $job->vid . ': ' . $job->name, 'url' => ['job/view', 'id' => $job->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Create Product'), 'url' => ['product/create', 'Product' => ArrayHelper::merge($_GET['Product'], ['product_type_id' => null]), 'ru' => ReturnUrl::getRequestToken()]];
if ($productType) {
    foreach ($productType->getBreadcrumb() as $breadcrumb) {
        $this->params['breadcrumbs'][] = [
            'label' => $breadcrumb->name,
            'url' => $breadcrumb->id == $productType->id ? false : ['product/create', 'Product' => ArrayHelper::merge($_GET['Product'], ['product_type_id' => $breadcrumb->id]), 'ru' => ReturnUrl::getRequestToken()],
        ];
    }
}
//} else {
//    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products'), 'url' => ['index']];
//    $this->params['breadcrumbs'][] = $this->title;
//}
?>
<div class="product-create">

    <?php if (!$productType || $productType->productTypes) { ?>
        <?php if ($productType) { ?>
            <div class="row">
                <div class="col-md-6">
                    <?= $this->render('/note/_index', ['model' => $model->product->productType, 'showActions' => false]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->render('/attachment/_index', ['model' => $model->product->productType, 'showActions' => false]) ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Product Type'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                /** @var ProductType[] $productTypes */
                $productTypes = $productType ? $productType->productTypes : ProductType::find()->notDeleted()->andWhere(['parent_id' => null])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all();
                foreach ($productTypes as $productType) {
                    if (!$productType->checkAccess('read')) {
                        continue;
                    }
                    $items[] = [
                        'label' => Html::img($productType->getImageSrc(), ['width' => '150', 'height' => '150']) . '<br>' . $productType->name,
                        'url' => ['product/create', 'Product' => ArrayHelper::merge($_GET['Product'], ['product_type_id' => $productType->id]), 'ru' => ReturnUrl::getRequestToken()],
                        'encode' => false,
                    ];
                }
                echo Nav::widget([
                    'items' => $items,
                    'options' => ['class' => 'nav-pills', 'style' => 'text-align:center;'],
                ]);
                ?>
            </div>
        </div>
    <?php } else { ?>
        <?php
        echo $this->render('_form', [
            'model' => $model,
        ]);
        ?>
    <?php } ?>

</div>
