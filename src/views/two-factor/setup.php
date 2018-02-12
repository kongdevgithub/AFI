<?php
/**
 * @var yii\web\View $this
 * @var \app\models\form\AuthyVerifyForm $model
 */

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Two Factor Setup');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="two-factor-setup">


    <div class="box box-default">
        <div class="box-body">

            <div class="row">
                <div class="col-md-6">
                    <p><?= Yii::t('app', 'Download Google Authenticator for {android}, {iphone} or {chrome}, then scan the barcode to add your account.', [
                            'android' => Html::a(Yii::t('app', 'Android'), 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2', ['target' => '_blank']),
                            'iphone' => Html::a(Yii::t('app', 'iPhone'), 'https://itunes.apple.com/us/app/google-authenticator/id388497605', ['target' => '_blank']),
                            'chrome' => Html::a(Yii::t('app', 'Chrome'), 'https://chrome.google.com/webstore/detail/authenticator/bhghoamapcdpbohphigoooaddinpkbai/related?hl=en', ['target' => '_blank']),
                        ]);
                        ?></p>
                    <?php
                    $form = ActiveForm::begin();
                    echo Html::hiddenInput('ru', $ru);
                    echo $form->errorSummary($model);
                    echo $form->field($model, 'code')->textInput()->label(false)->hint(Yii::t('app', 'Enter the code from your authenticator app.'));
                    echo Html::submitButton(Yii::t('app', 'Verify'), ['class' => 'btn btn-success']);
                    ActiveForm::end();
                    ?>
                </div>
                <div class="col-md-2 text-center">
                    <?= Html::a(Yii::t('app', 'Android'), 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2', ['target' => '_blank']) ?>
                    <br>
                    <?= Yii::$app->twoFactor->barcode(); ?><br>
                </div>
                <div class="col-md-2 text-center">
                    <?= Html::a(Yii::t('app', 'iPhone'), 'https://itunes.apple.com/us/app/google-authenticator/id388497605', ['target' => '_blank']) ?>
                    <br>
                    <?= Yii::$app->twoFactor->barcode(true); ?><br>
                </div>
                <div class="col-md-2 text-center">
                    <?= Html::a(Yii::t('app', 'Chrome'), 'https://chrome.google.com/webstore/detail/authenticator/bhghoamapcdpbohphigoooaddinpkbai/related?hl=en', ['target' => '_blank']) ?>
                    <br>
                    <code>
                        <?= Yii::$app->user->identity->two_factor['secret']; ?>
                    </code>
                </div>
            </div>
        </div>
    </div>

</div>
