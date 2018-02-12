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
 * @var app\models\form\PickupPackageForm $model
 * @var ActiveForm $form
 */

$this->title = $model->pickup->getLinkText();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->vid . ': ' . $model->job->name, 'url' => ['/job/view', 'id' => $model->job->id]];
//$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->id . ': ' . $model->name, 'url' => ['/product/view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="pickup-package">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Add Package'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'PickupPackage',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'action' => ['package', 'id' => $model->pickup->id],
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
            echo $form->field($model, 'package_ids')->textarea();
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>

