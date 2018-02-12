<?php

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Installer $model
 * @var kartik\form\ActiveForm $form
 */

?>

<div class="installer-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Installer',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Installer') ?></h3>
        </div>
        <div class="box-body">
            
    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('goldoc', 'Create') : Yii::t('goldoc', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php //echo if($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('goldoc', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
