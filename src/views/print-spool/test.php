<?php
/**
 * @var $this yii\web\View
 * @var $model \app\models\form\TestPrintForm
 */

use app\components\PrintSpool;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Test/System Print');
$this->params['heading'] = $this->title;
?>

<div class="print-spool-test">

    <?php
    $form = ActiveForm::begin([
        'id' => 'TestPrint',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $form->errorSummary($model);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
    echo $form->field($model, 'print_barcode')->checkbox();
    echo $form->field($model, 'print_system_barcode')->checkbox();
    echo $form->field($model, 'print_pdf')->checkbox();
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>


