<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/eeda5c365686c9888dbc13dbc58f89a1
 *
 * @package default
 */


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 *
 * @var yii\web\View $this
 * @var app\models\search\NotificationSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="notification-search">

    <?php $form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
	]); ?>

    		<?php echo $form->field($model, 'id') ?>

		<?php echo $form->field($model, 'model_name') ?>

		<?php echo $form->field($model, 'model_id') ?>

		<?php echo $form->field($model, 'title') ?>

		<?php echo $form->field($model, 'body') ?>

		<?php // echo $form->field($model, 'type') ?>

		<?php // echo $form->field($model, 'created_by') ?>

		<?php // echo $form->field($model, 'created_at') ?>

		<?php // echo $form->field($model, 'updated_by') ?>

		<?php // echo $form->field($model, 'updated_at') ?>

		<?php // echo $form->field($model, 'deleted_at') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('cruds', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('cruds', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
