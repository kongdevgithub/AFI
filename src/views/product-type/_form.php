<?php

use app\components\quotes\products\BaseProductQuote;
use app\models\ProductType;
use kartik\select2\Select2;
use kartik\file\FileInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductType $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="product-type-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ProductType',
        //'layout' => 'horizontal',
        'enableClientValidation' => false,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Product Type'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'parent_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'parent_id',
                'data' => ProductType::getDropdownOpts(),
                'options' => [
                    'placeholder' => '',
                    'multiple' => false,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'quote_class')->dropDownList(BaseProductQuote::opts(), [
                'prompt' => '',
            ]) ?>
            <?= $form->field($model, 'complexity')->dropDownList($model::complexityOpts(), [
                'prompt' => '',
            ]) ?>

            <?= $form->field($model, 'imageFile')->widget(FileInput::className(), [
                'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'showPreview' => true,
                    'showCaption' => false,
                    'showRemove' => true,
                    'showUpload' => false,
                ],
            ]); ?>

            <?= $form->field($model, 'config')->textarea() ?>
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
