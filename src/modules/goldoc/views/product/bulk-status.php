<?php

use app\components\ReturnUrl;
use app\modules\goldoc\models\Product;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var \app\modules\goldoc\models\form\BulkProductStatusForm $model
 * @var ActiveForm $form
 */

$this->title = Yii::t('goldoc', 'Product') . ': ' . Yii::t('goldoc', 'Bulk Status');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Bulk Status');

$product = new Product();
$product->sendToStatus(null);
$product->enterWorkflow(explode('/', $model->old_status)[0]);
$product->status = $model->old_status;
$product->initStatus();
?>

<div class="product-bulk-staus">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Product Status'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $statusDropDownData = $product->getStatusDropDownData(false);
            $form = ActiveForm::begin([
                'id' => 'Product',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'encodeErrorSummary' => false,
            ]);
            echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
            echo $form->errorSummary($model);

            foreach ($model->ids as $id) {
                echo Html::hiddenInput('ids[]', $id);
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
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>


            <?php JavaScript::begin(['position' => View::POS_END]) ?>
            <script>
                var $status = $('#bulkproductstatusform-new_status'),
                    oldStatus = '<?=$model->old_status?>'.split('/')[1];
                $status.change(function () {
                    var status = $status.val().split('/')[1];
                });
                $status.change();
            </script>
            <?php JavaScript::end() ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Products Selected'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $list = [];
            foreach ($model->getProducts() as $product) {
                $list[] = $product->getStatusButton() . ' ' . $product->getTitle();
            }
            echo Html::ul($list, ['encode' => false, 'class' => 'list-unstyled']);
            ?>
        </div>
    </div>

</div>

