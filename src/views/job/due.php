<?php

use app\components\ReturnUrl;
use app\models\Correction;
use app\models\User;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Due');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="job-due">

    <?php
    $changedAlertEmails = $model->getChangedAlertEmails();
    if ($changedAlertEmails) {
        $users = [];
        foreach ($changedAlertEmails as $email) {
            $users[] = User::findOne(['email' => $email]);
        }
        echo Alert::widget([
            'body' => '<p>' . Yii::t('app', 'This item is in a critical stage of production.  Please consider advising the following people of your changes:') . '</p>'
                . Html::ul(ArrayHelper::map($users, 'id', 'label')),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    }
    ?>

    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['due', 'id' => $model->id],
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

    if ($changedAlertEmails) {
        echo $form->field($model, 'correction_reason')->dropDownList(Correction::optsReason(), ['prompt' => '']);
    }
    echo $form->field($model, 'production_days')->textInput();
    echo $form->field($model, 'prebuild_days')->textInput();
    echo $form->field($model, 'freight_days')->textInput();
    echo $form->field($model, 'due_date')->widget(DatePicker::className(), [
        'layout' => '{picker}{input}',
        'options' => ['class' => 'form-control'],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            //'orientation' => 'top left',
        ],
    ]);
    echo $form->field($model, 'installation_date')->widget(DatePicker::className(), [
        'layout' => '{picker}{input}',
        'options' => ['class' => 'form-control'],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            //'orientation' => 'top left',
        ],
    ]);

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>

