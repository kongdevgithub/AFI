<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="item-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
