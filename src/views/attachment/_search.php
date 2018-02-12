<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
* @var yii\web\View $this
* @var app\models\search\AttachmentSearch $model
* @var yii\widgets\ActiveForm $form
*/
?>

<div class="attachment-search">

    <?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    ]); ?>

    		<?= $form->field($model, 'id') ?>

		<?= $form->field($model, 'model_name') ?>

		<?= $form->field($model, 'model_id') ?>

		<?= $form->field($model, 'filename') ?>

		<?= $form->field($model, 'extension') ?>

		<?php // echo $form->field($model, 'filetype') ?>

		<?php // echo $form->field($model, 'filesize') ?>

		<?php // echo $form->field($model, 'notes') ?>

		<?php // echo $form->field($model, 'sort_order') ?>

		<?php // echo $form->field($model, 'created_at') ?>

		<?php // echo $form->field($model, 'created_by') ?>

		<?php // echo $form->field($model, 'updated_by') ?>

		<?php // echo $form->field($model, 'updated_at') ?>

		<?php // echo $form->field($model, 'deleted_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('cruds', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('cruds', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
