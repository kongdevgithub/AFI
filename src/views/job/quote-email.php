<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
use dektrium\user\models\User;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use zhuravljov\widgets\DatePicker;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobQuoteEmailForm $model
 * @var ActiveForm $form
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->id . ': ' . $model->job->name, 'url' => ['view', 'id' => $model->job->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Quote Email');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="job-quote-email">
    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['quote-email', 'id' => $model->job->id],
        'encodeErrorSummary' => false,
        'fieldConfig' => [
            'errorOptions' => [
                'encode' => false,
                'class' => 'help-block',
            ],
        ],
    ]);
    echo Html::hiddenInput('ru', $ru);
    echo $model->errorSummary($form);
    echo $form->field($model, 'email_address')->textInput();

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Send Email'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['view', 'id' => $model->job->id]), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>