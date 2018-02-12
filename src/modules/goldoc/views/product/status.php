<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use dektrium\user\models\User;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use zhuravljov\widgets\DatePicker;

/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 * @var ActiveForm $form
 */

$this->title = Yii::t('goldoc', 'Product') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Status');
?>

<div class="product-status">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Product Status'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $statusDropDownData = $model->getStatusDropDownData(false);
            $form = ActiveForm::begin([
                'id' => 'Product',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'action' => ['status', 'id' => $model->id],
                'encodeErrorSummary' => false,
                'fieldConfig' => [
                    'errorOptions' => [
                        'encode' => false,
                        'class' => 'help-block',
                    ],
                ],
            ]);
            echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
            echo $form->errorSummary($model);

            //echo $form->field($model, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']])->label(false);
            echo $form->field($model, 'status')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'status',
                'data' => $statusDropDownData['items'],
                'options' => [
                    'multiple' => false,
                    'options' => $statusDropDownData['options'],
                ],
                'pluginOptions' => [
                    'templateResult' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                    'templateSelection' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                    'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                ],
            ])->label(false);

            echo '<div id="product-status-change">';
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('goldoc', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            echo '</div>';
            ActiveForm::end();
            ?>
            <?php JavaScript::begin() ?>
            <script>
                //var $status = $('#product-status'),
                //    status = $status.val();
                //$status.change();
            </script>
            <?php JavaScript::end() ?>
        </div>
    </div>

</div>

