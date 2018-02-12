<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Postcode $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="postcode-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Postcode',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <?php $this->beginBlock('main'); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'postcode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'state')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?>


    <?php $this->endBlock(); ?>

    <?= Tabs::widget([
        'encodeLabels' => false,
        'items' => [
            [
                'label' => 'Postcode',
                'content' => $this->blocks['main'],
                'active' => true,
            ],
        ],
    ]); ?>
    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php if($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
