<?php

/**
 * @var yii\web\View $this
 * @var array $ids
 * @var app\models\form\PackagePickupForm $model
 */
use app\components\PrintSpool;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Assign Pickup to Packages');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['package/index']];
//$this->params['breadcrumbs'][] = ['label' => 'package-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="package-pickup">

    <?php
    $form = ActiveForm::begin([
        'id' => 'PackagePickup',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'action' => ['pickup', 'confirm' => true],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $model->errorSummary($form);
    foreach ($model->ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    echo $form->field($model, 'pickup_id')->dropDownList($model->optsPickup());
    ?>

    <div id="package-new" style="display: none;">
        <?php
        echo $form->field($model, 'assign_each_package_a_new_pickup')->checkbox();
        $statusDropDownData = (new \app\models\Pickup())->getStatusDropDownData(false);
        echo $form->field($model, 'status')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'status',
            'data' => $statusDropDownData['items'],
            'options' => [
                'multiple' => false,
                'options' => $statusDropDownData['options'],
            ],
            'pluginOptions' => [
                'templateResult' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                'templateSelection' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                'escapeMarkup' => new JsExpression("function(m) { return m; }"),
            ],
        ]);
        echo $form->field($model, 'carrier_id')->dropDownList($model->optsCarrier(), ['prompt' => '']);
        echo $form->field($model, 'carrier_ref')->textInput();
        ?>
    </div>

    <div id="package-print" style="display: none;">
        <?php
        echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
        echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
        ?>
    </div>

    <div id="package-upload_freight" style="display: none;">
        <?php
        echo $form->field($model, 'upload_my_freight')->checkbox();
        echo $form->field($model, 'upload_cope_freight')->checkbox();
        ?>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>
        $('#packagepickupform-pickup_id').change(function () {
            var pickup_id = $(this).val(),
                $packageNew = $('#package-new'),
                $packagePrint = $('#package-print'),
                $packageUploadFreight = $('#package-upload_freight');

            $packageNew.hide();
            if (pickup_id === 'new') {
                $packageNew.show();
            }
            $packagePrint.hide();
            if (pickup_id !== 'none') {
                $packagePrint.show();
            }
            $packageUploadFreight.hide();
            if (pickup_id !== 'none') {
                $packageUploadFreight.show();
            }
        });
    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

