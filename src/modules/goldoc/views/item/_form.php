<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/4b7e79a8340461fe629a6ac612644d03
 *
 * @package default
 */


use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Item $model
 * @var kartik\form\ActiveForm $form
 */
?>

<div class="item-form">

    <?php $form = ActiveForm::begin([
		'id' => 'Item',
		//'type' => ActiveForm::TYPE_HORIZONTAL,
	]); ?>

    <?php echo Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Item') ?></h3>
        </div>
        <div class="box-body">

    <?php echo $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?php echo Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('goldoc', 'Create') : Yii::t('goldoc', 'Save')), [
		'id' => 'save-' . $model->formName(),
		'class' => 'btn btn-success'
	]); ?>
    <?php //echo if($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('goldoc', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
