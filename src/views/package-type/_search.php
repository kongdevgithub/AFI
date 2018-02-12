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
 * @var app\models\search\PackageTypeSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div id="package-type-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="package-type-searchModalLabel" aria-hidden="true">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'id') ?>

    <?php echo $form->field($model, 'name') ?>

    <?php echo $form->field($model, 'type') ?>

    <?php echo $form->field($model, 'width') ?>

    <?php echo $form->field($model, 'length') ?>

    <?php // echo $form->field($model, 'height') ?>

    <?php // echo $form->field($model, 'dead_weight') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('cruds', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('cruds', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
