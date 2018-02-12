<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Address $model
 */

?>

<div class="address-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Address',
        'type' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Address'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            echo '<div class="address">';
            echo $form->field($model, 'name')->textInput([
                'class' => 'form-control address-name',
            ]);
            echo $form->field($model, 'street')->textarea([
                'class' => 'form-control address-street',
            ]);
            echo $form->field($model, 'postcode')->textInput([
                'class' => 'form-control address-postcode',
            ]);
            echo $form->field($model, 'city')->textInput([
                'class' => 'form-control address-city',
            ]);
            echo $form->field($model, 'state')->textInput([
                'class' => 'form-control address-state',
            ]);
            echo $form->field($model, 'country')->textInput([
                'class' => 'form-control address-country',
            ]);
            echo '</div>';
            $this->render('/postcode/_ajax_lookup_script', ['formType' => $form->type, 'label' => false]);
            ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success',
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?php if (!$model->isNewRecord) echo Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
