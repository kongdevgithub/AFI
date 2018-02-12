<?php

use app\components\fields\BaseField;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\ReturnUrl;
use yii\helpers\Json;

/**
 * @var yii\web\View $this
 * @var app\models\Option $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="option-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Option',
        //'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Option'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'field_class')->dropDownList(BaseField::opts(), ['prompt' => '']) ?>

            <?= $form->field($model, 'field_config')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?= Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
