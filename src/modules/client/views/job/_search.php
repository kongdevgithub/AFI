<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\search\JobSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="job-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="job-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="job-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?= Yii::t('app', 'Search') . ' ' . Yii::t('app', 'Jobs') ?>                </h4>
            </div>
            <div class="modal-body">
                <?php
                echo $form->field($model, 'id');

                echo $form->field($model, 'name');

                //echo $form->field($model, 'job_type_id')->dropDownList([]);
                //echo $form->field($model, 'company_id')->dropDownList([]);
                //echo $form->field($model, 'contact_id')->dropDownList([]);
                //echo $form->field($model, 'staff_rep_id')->dropDownList([]);
                //echo $form->field($model, 'staff_csr_id')->dropDownList([]);
                //echo $form->field($model, 'rollout_id');
                //echo $form->field($model, 'price_structure_id')->dropDownList([]);
                //echo $form->field($model, 'account_term_id')->dropDownList([]);
                //echo $form->field($model, 'due_date');

                $statusDropDownData = $model->getStatusDropDownData(false);
                echo $form->field($model, 'status')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'status',
                    'data' => $statusDropDownData['items'],
                    'options' => [
                        'multiple' => true,
                    ],
                ]);

                echo $form->field($model, 'invoice_reference');

                echo $form->field($model, 'purchase_order');
                ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
