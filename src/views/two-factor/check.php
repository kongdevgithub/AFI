<?php
/**
 * @var yii\web\View $this
 * @var \app\models\form\AuthyVerifyForm $model
 */

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Two Factor Check');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="two-factor-check">

    <div class="box box-default">
        <div class="box-body">
            <?php
            $form = ActiveForm::begin();
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($model);
            echo $form->field($model, 'code')->textInput()->label(false)->hint(Yii::t('app', 'Enter the code from your authenticator app.'));
            echo Html::submitButton(Yii::t('app', 'Verify'), ['class' => 'btn btn-success']);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>
