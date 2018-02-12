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
 * @var app\models\Contact $model
 * @var yii\bootstrap\ActiveForm $form
 */

$select2Options = [
    'multiple' => false,
    'theme' => 'krajee',
    'placeholder' => '',
    'language' => 'en-US',
    'width' => '100%',
    'allowClear' => true,
];
?>

<div class="contact-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Contact',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Contact'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'default_company_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'default_company_id',
                'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->default_company_id])->all(), 'id', 'name'),
                'options' => $select2Options,
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

            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'fax')->textInput(['maxlength' => true]) ?>

        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?= Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
