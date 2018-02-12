<?php
/**
 * @var yii\web\View $this
 * @var app\models\form\JobArtworkApprovalForm $model
 * @var string $key
 */
use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Accept Artwork') . ' ' . $model->job->getTitle();

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();

$approvalText = Yii::t('app', 'Accept All Artwork');
foreach ($model->job->products as $product) {
    foreach ($product->items as $item) {
        if ($item->quantity && explode('/', $item->status)[1] == 'change') {
            $approvalText = Yii::t('app', 'Accept Remaining Artwork');
        }
    }
}
?>

<div class="approval-artwork-approval">
    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['artwork-approval', 'id' => $model->job->id, 'key' => $key],
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
    echo Html::tag('p', Yii::t('app', 'I accept all Artwork for Job #{job}.', [
        'job' => $model->job->id,
    ]));
    echo $form->field($model, 'full_name')->textInput();

    echo Html::submitButton('<span class="fa fa-check"></span> ' . $approvalText, [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['artwork', 'id' => $model->job->id]), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>

