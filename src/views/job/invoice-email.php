<?php

use app\components\ReturnUrl;
use dosamigos\tinymce\TinyMce;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobInvoiceEmailForm $model
 * @var ActiveForm $form
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->id . ': ' . $model->job->name, 'url' => ['view', 'id' => $model->job->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Invoice Email');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="job-invoice-email">
    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['invoice-email', 'id' => $model->job->id],
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

    echo $this->render('/job/_invoice-email-details', ['model' => $model->job]);

    echo $form->field($model, 'email_address')->textInput()->hint(Yii::t('app', 'Leave blank to send to the default address.'));
    echo $form->field($model->job, 'invoice_email_text')->widget(TinyMce::className(), [
        'options' => ['rows' => 8],
        'clientOptions' => [
            'menubar' => false,
            'toolbar' => 'styleselect | bold italic | bullist numlist outdent indent | code',
            'style_formats' => [
                ['title' => 'heading', 'block' => 'h3'],
                ['title' => 'lead', 'block' => 'p', 'styles' => ['font-size' => '17px', 'padding' => '', 'background-color' => '']],
                ['title' => 'paragraph', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '', 'background-color' => '']],
                ['title' => 'callout', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '15px', 'background-color' => '#ecf8ff']],
                ['title' => 'link', 'inline' => 'a', 'styles' => ['color' => '#2ba6cb', 'font-weight' => 'bold']],
            ],
            'plugins' => [
                'advlist autolink lists link charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste',
            ],
        ]
    ])
        ->hint(Yii::t('app', 'The {contact_first_name}, {job_label}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.'))
        ->label(false);

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Send Email'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['view', 'id' => $model->job->id]), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>