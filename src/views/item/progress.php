<?php

use app\components\PrintSpool;
use app\models\Item;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemProgressForm $model
 * @var ActiveForm $form
 */

$this->title = Yii::t('app', 'Item Progress');

$item = new Item;
$item->sendToStatus(null);
$item->enterWorkflow(explode('/', $model->old_status)[0]);
$item->status = $model->old_status;
$item->initStatus();
?>

<div class="item-progress">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Item Status'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $statusDropDownData = $item->getStatusDropDownData(false);
            $form = ActiveForm::begin([
                'id' => 'Item',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'encodeErrorSummary' => false,
            ]);
            echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
            echo $form->errorSummary($model);

            if ($model->job_id) {
                echo Html::hiddenInput('job_id', $model->job_id);
            }
            if ($model->item_ids) {
                foreach ($model->item_ids as $item_id) {
                    echo Html::hiddenInput('ids[]', $item_id);
                }
            }

            echo $form->field($model, 'old_status')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'old_status',
                'data' => $statusDropDownData['items'],
                'options' => [
                    'multiple' => false,
                    'options' => $statusDropDownData['options'],
                    'disabled' => true,
                ],
                'pluginOptions' => [
                    'templateResult' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                    'templateSelection' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                    'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                ],
            ])->label(false);

            //echo $form->field($model, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']])->label(false);
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

            <?php
            $optsMachine = $model->optsMachine();
            if ($optsMachine) { ?>
                <div id="item-machine" style="display:none;">
                    <?php
                    echo $form->field($model, 'machine_id')->dropDownList($optsMachine, ['prompt' => '']);
                    echo $form->field($model, 'machine_details')->textarea();
                    ?>
                </div>
            <?php } ?>

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


            <?php JavaScript::begin(['position' => View::POS_END]) ?>
            <script>
                var $status = $('#itemprogressform-new_status'),
                    $machine = $('#item-machine'),
                    $itemPrint = $('.field-itemprogressform-print'),
                    $itemPrintSpool = $('.field-itemprogressform-print_spool'),
                    oldStatus = '<?=$model->old_status?>'.split('/')[1];
                $status.change(function () {
                    var status = $status.val().split('/')[1];
                    $machine.hide();
                    if (status === 'rip' || status === 'production') {
                        $machine.show();
                    }
                });
                $itemPrintSpool.hide();
                $itemPrint.find(':input').change(function () {
                    if ($itemPrint.find(':input:checked').length > 0) {
                        $itemPrintSpool.show();
                    } else {
                        $itemPrintSpool.hide();
                    }
                });
                $status.change();
            </script>
            <?php JavaScript::end() ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Items Selected'); ?></h3>
        </div>
        <div class="box-body">
            <?= Html::ul(ArrayHelper::map($model->getItems(), 'id', 'title')) ?>
        </div>
    </div>

</div>

