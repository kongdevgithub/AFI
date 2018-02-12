<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\search\PackageSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="package-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="package-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="package-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('app', 'Search') . ' ' . Yii::t('app', 'Packages') ?>                </h4>
            </div>
            <div class="modal-body">
                <?php
                echo $form->field($model, 'id');
                echo $form->field($model, 'pickup_id');
                //echo $form->field($model, 'status');
                echo $form->field($model, 'address__name');
                echo $form->field($model, 'address__street');
                echo $form->field($model, 'address__postcode');
                echo $form->field($model, 'address__city');
                echo $form->field($model, 'address__state');
                echo $form->field($model, 'address__country');
                echo $form->field($model, 'address__contact');
                echo $form->field($model, 'address__phone');
                ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
