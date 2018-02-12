<?php

use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\ShippingAddressImportForm $model
 */

$this->title = $model->model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');
?>

<div class="company-shipping-address-import">

    <?php $form = ActiveForm::begin([
        'id' => 'Address',
        'type' => 'horizontal',
        'enableClientValidation' => false,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'upload')->widget(FileInput::className(), [
        'options' => ['accept' => '*.csv'],
        'pluginOptions' => [
            'showPreview' => true,
            'showCaption' => false,
            'showRemove' => true,
            'showUpload' => false,
        ],
    ])->label(false); ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
