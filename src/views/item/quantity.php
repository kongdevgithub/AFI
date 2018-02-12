<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
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
 * @var app\models\Item $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Quantity');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="item-quantity">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Quantity'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Item',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($model);
            echo $form->field($model->product, 'quantity')->textInput([
                'disabled' => 'disabled',
            ])->label(Yii::t('app', 'Product Quantity'));
            echo $form->field($model, 'quantity')->textInput()->label(Yii::t('app', 'Item Quantity'));
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>

