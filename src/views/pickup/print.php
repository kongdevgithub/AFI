<?php

/**
 * @var yii\web\View $this
 * @var array $ids
 * @var app\models\form\PickupPrintForm $model
 */
use app\components\PrintSpool;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Print Pickups');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pickups'), 'url' => ['pickup/index']];
//$this->params['breadcrumbs'][] = ['label' => 'pickup-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="pickup-print">

    <?php
    $form = ActiveForm::begin([
        'id' => 'PickupPrint',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'action' => ['print', 'confirm' => true],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $form->errorSummary($model);
    foreach ($model->ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
    echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Print'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

