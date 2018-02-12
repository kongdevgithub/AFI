<?php

/**
 * @var yii\web\View $this
 * @var array $ids
 */
use app\components\PrintSpool;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'My Freight Pickup');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['package/index']];
//$this->params['breadcrumbs'][] = ['label' => 'package-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="pickup-my-freight">

    <?php
    $form = ActiveForm::begin([
        'id' => 'Pickup',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'action' => ['my-freight', 'confirm' => true],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    //echo $form->errorSummary($model);
    foreach ($ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Upload'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

