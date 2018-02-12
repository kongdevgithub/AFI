<?php
use app\components\Helper;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Status'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        if (Yii::$app->user->can('app_company_status')) {
            $statusDropDownData = $model->getStatusDropDownData(false);
            $form = ActiveForm::begin([
                'id' => 'Company',
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

            echo '<div id="company-status-change">';
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            echo '</div>';
            ActiveForm::end();
            ?>
            <?php \app\widgets\JavaScript::begin() ?>
            <script>
                //var $status = $('#company-status'),
                //    status = $status.val();
            </script>
            <?php \app\widgets\JavaScript::end() ?>
            <?php
        } else {
            echo $model->getWorkflowStatus()->getLabel();
        }
        ?>
    </div>
</div>

