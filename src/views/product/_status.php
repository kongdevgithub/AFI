<?php
use app\components\Helper;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Product Status'); ?></h3>
        <div class="box-tools pull-right text-right">
            <?php
            if ($model->getOldAttribute('status') == 'product/production') {
                echo Helper::getStatusButtonGroup($model->getStatusList());
            }
            ?>
        </div>
    </div>
    <div class="box-body">
        <?php
        $statusDropDownData = $model->getStatusDropDownData(false);
        $form = ActiveForm::begin([
            'id' => 'Product',
            'formConfig' => ['labelSpan' => 0],
            'enableClientValidation' => false,
            'action' => ['status', 'id' => $model->id],
            'encodeErrorSummary' => false,
            'fieldConfig' => [
                'errorOptions' => [
                    'encode' => false,
                    'class' => 'help-block',
                ],
            ],
        ]);
        echo Html::hiddenInput('ru', $ru);
        echo $form->errorSummary($model);

        //echo $form->field($model, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']])->label(false);
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
        ])->label(false);

        echo '<div id="product-status-change">';
        echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
            'id' => 'save-' . $model->formName(),
            'class' => 'btn btn-success'
        ]);
        echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['/job/view', 'id' => $model->job->id]), ['class' => 'btn btn-default']);
        echo '</div>';
        ActiveForm::end();
        ?>
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            //var $status = $('#product-status'),
            //    status = $status.val();
            //$status.change();
        </script>
        <?php \app\widgets\JavaScript::end() ?>
    </div>
</div>

