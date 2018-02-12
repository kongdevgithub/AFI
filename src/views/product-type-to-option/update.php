<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductTypeToOption $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Option') . ' ' . ($model->option ? $model->option->name : '') . ($model->productTypeToItemType ? ' (' . $model->productTypeToItemType->name . ')' : '');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['/product-type/index']];
foreach ($model->productType->getBreadcrumb() as $breadcrumb) {
    $this->params['breadcrumbs'][] = ['label' => $breadcrumb->name, 'url' => ['/product-type/view', 'id' => $breadcrumb->id]];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-type-to-option-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
