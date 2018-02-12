<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/4b7e79a8340461fe629a6ac612644d03
 *
 * @package default
 */


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \dmstr\bootstrap\Tabs;
use yii\helpers\StringHelper;

/**
 *
 * @var yii\web\View $this
 * @var app\models\Notification $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="notification-form">

    <?php $form = ActiveForm::begin([
		'id' => 'Notification',
		'layout' => 'horizontal',
		'enableClientValidation' => true,
		'errorSummaryCssClass' => 'error-summary alert alert-danger'
	]
);
?>

    <div class="">
        <?php $this->beginBlock('main'); ?>

        <p>


<!-- attribute id -->
			<?php echo $form->field($model, 'id')->textInput() ?>

<!-- attribute model_name -->
			<?php echo $form->field($model, 'model_name')->textInput(['maxlength' => true]) ?>

<!-- attribute model_id -->
			<?php echo $form->field($model, 'model_id')->textInput() ?>

<!-- attribute title -->
			<?php echo $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

<!-- attribute body -->
			<?php echo $form->field($model, 'body')->textarea(['rows' => 6]) ?>

<!-- attribute type -->
			<?php echo $form->field($model, 'type')->textInput(['maxlength' => true]) ?>

<!-- attribute deleted_at -->
			<?php echo $form->field($model, 'deleted_at')->textInput() ?>

<!-- attribute created_by -->
			<?php echo $form->field($model, 'created_by')->textInput() ?>

<!-- attribute created_at -->
			<?php echo $form->field($model, 'created_at')->textInput() ?>

<!-- attribute updated_by -->
			<?php echo $form->field($model, 'updated_by')->textInput() ?>

<!-- attribute updated_at -->
			<?php echo $form->field($model, 'updated_at')->textInput() ?>
        </p>
        <?php $this->endBlock(); ?>

        <?php echo
Tabs::widget(
	[
		'encodeLabels' => false,
		'items' => [
			[
				'label'   => Yii::t('models', 'Notification'),
				'content' => $this->blocks['main'],
				'active'  => true,
			],
		]
	]
);
?>
        <hr/>

        <?php echo $form->errorSummary($model); ?>

        <?php echo Html::submitButton(
	'<span class="glyphicon glyphicon-check"></span> ' .
	($model->isNewRecord ? Yii::t('cruds', 'Create') : Yii::t('cruds', 'Save')),
	[
		'id' => 'save-' . $model->formName(),
		'class' => 'btn btn-success'
	]
);
?>

        <?php ActiveForm::end(); ?>

    </div>

</div>
