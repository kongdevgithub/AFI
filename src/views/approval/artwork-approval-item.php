<?php
/**
 * @var yii\web\View $this
 * @var app\models\form\ItemArtworkApprovalForm $model
 * @var string $key
 */
use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Change Request') . ' #' . $model->item->product->job->getTitle();

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="approval-artwork-approval">
    <?php
    $form = ActiveForm::begin([
        'id' => 'Job',
        'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'action' => ['artwork-approval-item', 'id' => $model->item->product->job->id, 'item_id' => $model->item->id, 'key' => $key],
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
    echo $form->field($model, 'full_name')->textInput();
    echo $form->field($model, 'details')->textarea();

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Send Change Request'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['artwork', 'id' => $model->item->product->job->id]), ['class' => 'btn btn-default']);
    ActiveForm::end();
    ?>
</div>

