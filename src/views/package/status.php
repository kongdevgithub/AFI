<?php

/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */

$this->title = 'package-' . $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['package/index']];
//$this->params['breadcrumbs'][] = ['label' => 'package-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="product-status">

    <?= $this->render('_status', ['model' => $model]) ?>

</div>

