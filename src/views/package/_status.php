<?php
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Package Status'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $statusDropDownData = $model->getStatusDropDownData(false);
        $form = ActiveForm::begin([
            'id' => 'Package',
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
        echo Html::hiddenInput('ru', !empty($ru) ? $ru : ReturnUrl::getRequestToken());
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

        echo '<div id="package-status-change">';
        echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
            'id' => 'save-' . $model->formName(),
            'class' => 'btn btn-success'
        ]);
        echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['/package/view', 'id' => $model->id]), ['class' => 'btn btn-default']);
        echo '</div>';
        ActiveForm::end();
        ?>
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            //var $status = $('#package-status'),
            //    status = $status.val();
        </script>
        <?php \app\widgets\JavaScript::end() ?>
    </div>
</div>

