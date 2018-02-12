<?php

use app\components\PrintSpool;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\PackageItemForm $model
 */

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();

$fieldConfig = [
    'errorOptions' => [
        'encode' => false,
        'class' => 'help-block',
    ],
];
$form = ActiveForm::begin([
    'id' => 'Job',
    'formConfig' => ['labelSpan' => 0],
    'enableClientValidation' => false,
    //'action' => ['due', 'id' => $model->id],
    'encodeErrorSummary' => false,
    'fieldConfig' => $fieldConfig,
]);
echo Html::hiddenInput('ru', $ru);
echo $form->errorSummary($model);

echo $form->field($model, 'ids')->textarea();
echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool(), ['prompt' => '']);

echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
    'id' => 'save-' . $model->formName(),
    'class' => 'btn btn-success'
]);
ActiveForm::end();

JavaScript::begin();
?>
    <script>
        $('#packageitemform-ids').focus();
    </script>
<?php
JavaScript::end();
