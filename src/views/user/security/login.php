<?php

use dektrium\user\widgets\Connect;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\LoginForm $model
 * @var dektrium\user\Module $module
 */

$this->title = Yii::$app->name;
$this->params['heading'] = '';
//$this->params['breadcrumbs'][] = Yii::t('app', 'Sign in');
?>

<div class="user-login">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode(Yii::t('app', 'Sign in')) ?></h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
                'validateOnBlur' => false,
                'validateOnType' => false,
                'validateOnChange' => false,
                'type' => ActiveForm::TYPE_HORIZONTAL,
            ]) ?>

            <?= $form->field($model, 'login',
                ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
            );
            ?>

            <?= $form->field(
                $model,
                'password',
                ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']])
                ->passwordInput()
                ->hint(($module->enablePasswordRecovery ? Html::a(Yii::t('app', 'Forgot password?'), ['/user/recovery/request'], ['tabindex' => '5']) : '')) ?>

            <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '3']) ?>

            <?= Html::submitButton(
                Yii::t('app', 'Sign in'),
                ['class' => 'btn btn-primary btn-block', 'tabindex' => '4']
            ) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php if ($module->enableConfirmation): ?>
        <p class="text-center">
            <?= Html::a(Yii::t('app', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
        </p>
    <?php endif ?>
    <?php if ($module->enableRegistration): ?>
        <p class="text-center">
            <?= Html::a(Yii::t('app', 'Don\'t have an account? Sign up!'), ['/user/registration/register']) ?>
        </p>
    <?php endif ?>
    <?= Connect::widget([
        'baseAuthUrl' => ['/user/security/auth'],
    ]) ?>
</div>
