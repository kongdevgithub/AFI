<?php
/**
 * @var yii\web\View $this
 * @var app\models\form\JobQuoteApprovalForm $model
 * @var string $key
 */
use app\components\Helper;
use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Accept Quote') . ' ' . $model->job->getTitle();

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="approval-quote-approval">
    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['quote-approval', 'id' => $model->job->id, 'key' => $key],
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
    echo Html::tag('p', Yii::t('app', 'I accept Quote #{quote} for a total of ${total}.', [
        'quote' => $model->job->id,
        'total' => number_format($model->job->quote_total_price, 2),
    ]));
    echo $form->field($model, 'full_name')->textInput();;

    $startDate = Helper::getRelativeDate(date('Y-m-d'), $model->job->production_days + $model->job->prebuild_days + $model->job->freight_days + 1);
    echo $form->field($model, 'requested_due_date')->widget(DatePicker::className(), [
        'layout' => '{picker}{input}',
        'options' => ['class' => 'form-control'],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            'orientation' => 'top left',
            'startDate' => Helper::getRelativeDate(date('Y-m-d'), $model->job->production_days + $model->job->prebuild_days + $model->job->freight_days + 1),
        ],
    ])->hint(Yii::t('app', 'To set a date earlier than {date} please contact {contact} on {phone} or {email}.', [
        'date' => Yii::$app->formatter->asDate($startDate),
        'contact' => $model->job->staffRep->getLabel(),
        'phone' => $model->job->staffRep->profile->phone ? $model->job->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
        'email' => $model->job->staffRep->email,
    ]));

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Accept Quote'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['quote', 'id' => $model->job->id]), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>

