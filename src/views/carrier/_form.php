<?php

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Carrier $model
 * @var ActiveForm $form
 */
?>

<div class="carrier-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Carrier',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => false,
    ]);
    ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Carrier Details'); ?></h3>
        </div>
        <div class="box-body">
            <?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?php echo $form->field($model, 'my_freight_code')->textInput(['maxlength' => true]) ?>
            <?php echo $form->field($model, 'cope_freight_code')->textInput(['maxlength' => true]) ?>
            <?php echo $form->field($model, 'tracking_url')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php if ($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
