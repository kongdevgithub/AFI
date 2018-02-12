<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\search\ProductSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="product-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="product-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="product-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('app', 'Search') . ' ' . Yii::t('app', 'Products') ?>                </h4>
            </div>
            <div class="modal-body">
                <?= $form->field($model, 'id') ?>
                <?= $form->field($model, 'name') ?>
                <?= $form->field($model, 'job_id') ?>
                <?= $form->field($model, 'product_type_id')->dropDownList($model->optsProductType(), [
                    'prompt' => '',
                ]) ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
