<?php

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->vid . ': ' . $model->job->name, 'url' => ['/job/view', 'id' => $model->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->id . ': ' . $model->name, 'url' => ['/product/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Quantity');

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="product-quantity">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Quantity'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Product',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($model);

            //echo $form->field($model, 'preserve_unit_prices')->checkbox();
            echo $form->field($model, 'quantity')->textInput()->label(false);

            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>

