<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\RecoveryForm $model
 */

$this->title = Yii::t('user', 'Recover your password');
$this->params['heading'] = '';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-forgot">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'password-recovery-form',
                'enableAjaxValidation' => true,
                'enableClientValidation' => false,
                'type' => ActiveForm::TYPE_HORIZONTAL,
            ]); ?>

            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

            <?= Html::submitButton(Yii::t('user', 'Continue'), ['class' => 'btn btn-primary btn-block']) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
