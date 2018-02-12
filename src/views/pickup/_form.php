<?php

use app\models\Carrier;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="pickup-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Pickup',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'carrier_id')->widget(Select2::className(), [
        'name' => 'class_name',
        'model' => $model,
        'attribute' => 'carrier_id',
        'data' => ArrayHelper::map(Carrier::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
        'options' => [
            'placeholder' => '',
            'multiple' => false,
        ]
    ]) ?>

    <?= $form->field($model, 'carrier_ref')->textInput() ?>


    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?= Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
