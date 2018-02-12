<?php
use app\components\Helper;
use app\components\ReturnUrl;
use app\models\Carrier;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 */

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Pickup Status'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $statusDropDownData = $model->getStatusDropDownData(false);
        $form = ActiveForm::begin([
            'id' => 'Pickup',
            //'formConfig' => ['labelSpan' => 0],
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'enableClientValidation' => false,
            'action' => ['status', 'id' => $model->id],
            'encodeErrorSummary' => false,
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
        ]);

        echo $form->field($model, 'carrier_id')->widget(Select2::className(), [
            'name' => 'class_name',
            'model' => $model,
            'attribute' => 'carrier_id',
            'data' => ArrayHelper::map(Carrier::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
            'options' => [
                'placeholder' => '',
                'multiple' => false,
            ]
        ]);

        echo $form->field($model, 'carrier_ref')->textInput();
        if (!$model->emailed_at) {
            echo $form->field($model, 'send_email')->checkbox();
        }

        echo '<div id="pickup-status-change">';
        echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
            'id' => 'save-' . $model->formName(),
            'class' => 'btn btn-success'
        ]);
        echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['/pickup/view', 'id' => $model->id]), ['class' => 'btn btn-default']);
        echo '</div>';
        ActiveForm::end();
        ?>
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            //var $status = $('#pickup-status'),
            //    status = $status.val();
            //$status.change();
        </script>
        <?php \app\widgets\JavaScript::end() ?>
    </div>
</div>

