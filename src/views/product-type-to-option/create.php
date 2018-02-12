<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductTypeToOption $model
 */

$this->title = Yii::t('app', 'Create Option');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['/product-type/index']];
if ($model->productType) {
    foreach ($model->productType->getBreadcrumb() as $breadcrumb) {
        $this->params['breadcrumbs'][] = ['label' => $breadcrumb->name, 'url' => ['/product-type/view', 'id' => $breadcrumb->id]];
    }
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-type-to-option-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
