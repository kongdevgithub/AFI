<?php

use app\components\PrintSpool;
use app\components\ReturnUrl;
use app\models\Pickup;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var app\models\form\PickupProgressForm $model
 * @var ActiveForm $form
 */

$this->title = Yii::t('app', 'Pickup Progress');

$pickup = new Pickup;
$pickup->sendToStatus(null);
$pickup->enterWorkflow(explode('/', $model->status)[0]);
$pickup->status = $model->status;
$pickup->initStatus();
$model->new_status = $pickup->status;
?>

<div class="pickup-progress">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Pickup Status'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $statusDropDownData = $pickup->getStatusDropDownData(false);
            $form = ActiveForm::begin([
                'id' => 'Pickup',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'action' => ['progress', 'status' => $model->status],
                'encodeErrorSummary' => false,
            ]);
            echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
            echo $form->errorSummary($model);

            if ($model->ids) {
                foreach ($model->ids as $pickup_id) {
                    echo Html::hiddenInput('ids[]', $pickup_id);
                }
            }

            echo $form->field($model, 'new_status')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'new_status',
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
            ])->label(false);
            ?>


            <div id="send-email">
                <?php
                echo $form->field($model, 'send_email')->checkbox();
                ?>
            </div>

            <div id="unit-print">
                <?php
                echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
                echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
                ?>
            </div>

            <?php
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>

            <?php \app\widgets\JavaScript::begin(['position' => View::POS_END]) ?>
            <script>
                var $status = $('#pickupprogressform-new_status'),
                    $pickupPrint = $('.field-pickupprogressform-print'),
                    $pickupPrintSpool = $('.field-pickupprogressform-print_spool'),
                    oldStatus = '<?=$model->status?>'.split('/')[1];
                $pickupPrintSpool.hide();
                $pickupPrint.find(':input').change(function () {
                    if ($pickupPrint.find(':input:checked').length > 0) {
                        $pickupPrintSpool.show();
                    } else {
                        $pickupPrintSpool.hide();
                    }
                });
                $status.change();
            </script>
            <?php \app\widgets\JavaScript::end() ?>
        </div>
    </div>

</div>

