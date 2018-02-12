<?php

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Surcharge');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="job-surcharge">

    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['surcharge', 'id' => $model->id],
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
    echo $form->field($model, 'quote_surcharge_price')->textInput();
    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>

