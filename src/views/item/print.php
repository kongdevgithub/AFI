<?php

use app\components\PrintSpool;
use app\models\Item;
use app\models\ItemType;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemPrintForm $model
 */

$this->title = $model->item->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Items'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="item-print">

    <?php
    $form = ActiveForm::begin([
        'id' => 'ItemPrint',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $form->errorSummary($model);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    echo $form->field($model, 'print')->checkboxList($model->optsPrint());
    echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Print'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

