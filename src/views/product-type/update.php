<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductType $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['index']];
if ($model->parent) {
    foreach ($model->parent->getBreadcrumb() as $breadcrumb) {
        $this->params['breadcrumbs'][] = ['label' => $breadcrumb->name, 'url' => ['view', 'id' => $breadcrumb->id]];
    }
}
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="product-type-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
