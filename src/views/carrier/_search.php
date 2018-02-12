<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 *
 * @var yii\web\View $this
 * @var app\models\search\CarrierSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div id="carrier-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="carrier-searchModalLabel" aria-hidden="true">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'layout' => 'horizontal',
        'method' => 'get',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'offset' => 'col-sm-offset-3',
                'label' => 'col-sm-3',
                'wrapper' => 'col-sm-9',
                'error' => '',
                'hint' => '',
            ],
        ],
    ]); ?>

    <?php echo $form->field($model, 'id') ?>

    <?php echo $form->field($model, 'name') ?>

    <?php echo $form->field($model, 'created_at') ?>

    <?php echo $form->field($model, 'updated_at') ?>

    <?php echo $form->field($model, 'my_freight_code') ?>

    <?php echo $form->field($model, 'cope_freight_code') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('cruds', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('cruds', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
