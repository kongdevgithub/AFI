<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\search\AddressSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="address-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="address-searchModalLabel" aria-hidden="true">

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

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="address-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('app', 'Search') . ' ' . Yii::t('app', 'Addresses') ?>                </h4>
            </div>
            <div class="modal-body">
                <?= $form->field($model, 'id') ?>
                <?= $form->field($model, 'model_name') ?>
                <?= $form->field($model, 'model_id') ?>
                <?= $form->field($model, 'type') ?>
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'street') ?>
                <?= $form->field($model, 'postcode') ?>
                <?= $form->field($model, 'city') ?>
                <?= $form->field($model, 'state') ?>
                <?= $form->field($model, 'country') ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
