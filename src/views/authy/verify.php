<?php
/**
 * @var yii\web\View $this
 * @var \app\models\form\AuthyVerifyForm $model
 */

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Authy Verify');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="authy-verify">
    <?php
    $form = ActiveForm::begin();
    echo Html::hiddenInput('ru', $ru);

    echo $form->errorSummary($model);
    echo $form->field($model, 'token')->textInput()->hint(Yii::t('app', 'No SMS? {sms} or {phone}', [
        'sms' => Html::a(Yii::t('app', 'Request another SMS'), ['sms', 'ru' => ReturnUrl::getToken()]),
        'phone' => Html::a(Yii::t('app', 'Phone Call'), ['phone', 'ru' => ReturnUrl::getToken()]),
    ]));

    echo Html::submitButton(Yii::t('app', 'Verify'), ['class' => 'btn btn-success']);

    ActiveForm::end();
    ?>
</div>
