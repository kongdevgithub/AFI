<?php

use app\models\Company;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Rollout $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="rollout-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Rollout',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>


    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Rollout'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'company_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'company_id',
                'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->company_id])->all(), 'id', 'name'),
                'options' => [
                    'multiple' => false,
                    'theme' => 'krajee',
                    'placeholder' => '',
                    'language' => 'en-US',
                    'width' => '100%',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax' => [
                        'url' => Url::to(['company/json-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                ],
            ]); ?>
        </div>
    </div>


    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?= Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?php if (!$model->isNewRecord) Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
