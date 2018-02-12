<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\ComponentType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Component $model
 * @var kartik\form\ActiveForm $form
 */

?>

<div class="component-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Component',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Component'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'brand')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'component_type_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'component_type_id',
                'data' => ArrayHelper::map(ComponentType::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                'options' => [
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]); ?>

            <?= $form->field($model, 'quote_class')->dropDownList(BaseComponentQuote::opts(), ['prompt' => Yii::t('app', 'Inherit')]) ?>
            <?= $form->field($model, 'make_ready_cost')->textInput() ?>
            <?= $form->field($model, 'unit_cost')->textInput() ?>
            <?= $form->field($model, 'minimum_cost')->textInput() ?>
            <?= $form->field($model, 'quantity_factor')->textarea()->hint(Yii::t('app', 'Format:<br>quantity1 factor1<br>quantity2 factor2<br><br>Eg:<br>0 2<br>10 1.8<br>100 1.5')) ?>
            <?= $form->field($model, 'unit_dead_weight')->textInput() ?>
            <?= $form->field($model, 'unit_cubic_weight')->textInput() ?>
            <?= $form->field($model, 'unit_of_measure')->textInput() ?>
            <?= $form->field($model, 'track_stock')->checkbox()->hint(Yii::t('app', 'Tracked in Dear Inventory management.')) ?>
            <?= $form->field($model, 'quality_check')->checkbox()->hint(Yii::t('app', 'Requires check before item can proceed quality.')) ?>
            <?= $form->field($model, 'quality_code')->textInput()->hint(Yii::t('app', 'RFID required to pass quality check, leave blank for none.')) ?>
            <?= $form->field($model, 'component_config')->textarea() ?>
            <?= $form->field($model, 'notes')->textarea() ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
