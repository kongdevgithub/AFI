<?php

use app\models\User;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 */

$this->title = $model->product->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="product-update">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <?php
    //$emails = $model->product->getChangedAlertEmails();
    //if ($emails) {
    //    $users = [];
    //    foreach ($emails as $email) {
    //        $users[] = User::findOne(['email' => $email]);
    //    }
    //    echo Alert::widget([
    //        'body' => '<p>' . Yii::t('app', 'This item is in a critical stage of production.  Please consider advising the following people of your changes:') . '</p>'
    //            . Html::ul(ArrayHelper::map($users, 'id', 'label')),
    //        'options' => ['class' => 'alert-danger'],
    //        'closeButton' => false,
    //    ]);
    //}
    ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
