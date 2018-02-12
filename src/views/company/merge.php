<?php

use app\models\Company;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Merge');
?>
<div class="company-merge">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <p>This will move the following to the merge_id:</p>
    <ul>
        <li>Jobs</li>
        <li>Contacts</li>
        <li>Addresses</li>
        <li>Notes</li>
    </ul>

    <div class="company-merge-form">

        <?php $form = ActiveForm::begin([
            'id' => 'Company',
            //'type' => 'horizontal',
            'formConfig' => [
                'labelSpan' => 0,
            ],
            'enableClientValidation' => false,
        ]); ?>

        <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

        <?= $form->errorSummary($model); ?>

        <?= $form->field($model, 'merge_id')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'merge_id',
            'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->merge_id])->all(), 'id', 'name'),
            'options' => [
                'multiple' => false,
                'theme' => 'krajee',
                'placeholder' => '',
                'language' => 'en-US',
                'width' => '100%',
                //'allowClear' => true,
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

</div>
