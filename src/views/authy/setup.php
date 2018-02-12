<?php
/**
 * @var yii\web\View $this
 * @var \app\models\form\AuthySetupForm $model
 */

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Two Factor Authentication - Setup');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="authy-setup">
    <?php
    $form = ActiveForm::begin();
    echo Html::hiddenInput('ru', $ru);

    echo $form->errorSummary($model);
    echo $form->field($model, 'phone')->textInput();
    echo $form->field($model, 'country_code')->textInput();

    echo Html::submitButton(Yii::t('app', 'Continue Setup'), [
        'class' => 'btn btn-success'
    ]);

    ActiveForm::end();
    ?>
</div>
