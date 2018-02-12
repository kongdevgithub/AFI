<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\search\InstallerSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="installer-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="installer-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="installer-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('goldoc', 'Search') . ' ' . Yii::t('goldoc', 'Installers') ?>                </h4>
            </div>
            <div class="modal-body">
                <?= $form->field($model, 'id') ?>
                <?= $form->field($model, 'code') ?>
                <?= $form->field($model, 'name') ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('goldoc', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
