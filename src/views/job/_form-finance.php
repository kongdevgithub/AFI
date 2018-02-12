<?php

use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

?>


<?php $form = ActiveForm::begin([
    'id' => 'Job',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => [
        'labelSpan' => 3,
    ],
    'enableClientValidation' => false,
    //'options' => ['enctype' => 'multipart/form-data'],
]); ?>
<?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>
<?= $form->errorSummary($model); ?>

<div class="row">
    <div class="col-md-6">
        <?php
        echo $form->field($model, 'quote_freight_price')->textInput()->label(Yii::t('app', 'Freight'));
        echo $form->field($model, 'quote_surcharge_price')->textInput()->label(Yii::t('app', 'Surcharge'));
        echo $form->field($model, 'quote_discount_price')->textInput()->label(Yii::t('app', 'Discount'));
        ?>
    </div>
    <div class="col-md-6">
        <?php
        echo $form->field($model, 'invoice_sent')->widget(DatePicker::className(), [
            'layout' => '{picker}{input}',
            'options' => ['class' => 'form-control'],
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
                //'orientation' => 'top left',
            ],
        ]);

        echo $form->field($model, 'invoice_amount')->textInput()->hint(Yii::t('app', 'Quoted {total} ex GST', [
            'total' => '$' . number_format($model->quote_total_price - $model->quote_tax_price, 2),
        ]));

        echo $form->field($model, 'invoice_reference')->textInput();

        echo $form->field($model, 'invoice_paid')->widget(DatePicker::className(), [
            'layout' => '{picker}{input}',
            'options' => ['class' => 'form-control'],
            'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'format' => 'yyyy-mm-dd',
                //'orientation' => 'top left',
            ],
        ]);
        ?>
    </div>
</div>

<?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
    'id' => 'save-' . $model->formName(),
    'class' => 'btn btn-success'
]); ?>

<?php ActiveForm::end(); ?>


