<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\search\ProductToOptionSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="product-to-option-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="product-to-option-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="product-to-option-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('app', 'Search') . ' ' . Yii::t('app', 'ProductToOptions') ?>                </h4>
            </div>
            <div class="modal-body">
                <?= $form->field($model, 'id') ?>
                <?= $form->field($model, 'product_id') ?>
                <?= $form->field($model, 'item_id') ?>
                <?= $form->field($model, 'option_id') ?>
                <?= $form->field($model, 'product_type_to_option_id') ?>
                <?= $form->field($model, 'value') ?>
                <?= $form->field($model, 'sort_order') ?>
                <?= $form->field($model, 'quote_class') ?>
                <?= $form->field($model, 'quote_label') ?>
                <?= $form->field($model, 'quote_unit_cost') ?>
                <?= $form->field($model, 'quote_quantity') ?>
                <?= $form->field($model, 'quote_total_cost') ?>
                <?= $form->field($model, 'quote_make_ready_cost') ?>
                <?= $form->field($model, 'markup') ?>
                <?= $form->field($model, 'quote_total_price') ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
