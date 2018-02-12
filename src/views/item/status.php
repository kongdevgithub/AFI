<?php

use kartik\form\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemStatusForm $model
 * @var ActiveForm $form
 */

$this->title = $model->item->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->item->product->job->vid . ': ' . $model->item->product->job->name, 'url' => ['/job/view', 'id' => $model->item->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->item->product->id . ': ' . $model->item->product->name, 'url' => ['/product/view', 'id' => $model->item->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->item->id . ': ' . $model->item->name, 'url' => ['view', 'id' => $model->item->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="item-status">

    <?= $this->render('_status', ['model' => $model]) ?>

</div>

