<?php

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');
?>

<div class="contact-company-assign">

    <?php $form = ActiveForm::begin([
        'id' => 'CompanyAssign',
        'type' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'default_company_id')->widget(Select2::className(), [
        'model' => $model,
        'attribute' => 'default_company_id',
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
    ])->label(Yii::t('app', 'Company')); ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Assign Company'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
