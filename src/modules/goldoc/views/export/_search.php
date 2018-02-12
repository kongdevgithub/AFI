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
 * @var app\models\search\ExportSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="export-search">

    <?php $form = ActiveForm::begin([
		'action' => ['index'],
		'method' => 'get',
	]); ?>

    		<?php echo $form->field($model, 'id') ?>

		<?php echo $form->field($model, 'user_id') ?>

		<?php echo $form->field($model, 'model_name') ?>

		<?php echo $form->field($model, 'model_params') ?>

		<?php echo $form->field($model, 'status') ?>

		<?php // echo $form->field($model, 'total_rows') ?>

		<?php // echo $form->field($model, 'created_at') ?>

		<?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('cruds', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('cruds', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
