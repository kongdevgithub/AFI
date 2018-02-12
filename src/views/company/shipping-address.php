<?php

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 * @var app\models\Address $address
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');
?>

<div class="company-shipping-address">

    <?php $form = ActiveForm::begin([
        'id' => 'Address',
        'type' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($address); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Shipping Address'); ?></h3>
        </div>
        <div class="box-body">

            <?php
            echo '<div class="address">';
            echo $form->field($address, 'name')->textInput([
                'class' => 'form-control address-name',
            ]);
            echo $form->field($address, 'street')->textarea([
                'class' => 'form-control address-street',
            ]);
            echo $form->field($address, 'postcode')->textInput([
                'class' => 'form-control address-postcode',
            ]);
            echo $form->field($address, 'city')->textInput([
                'class' => 'form-control address-city',
            ]);
            echo $form->field($address, 'state')->textInput([
                'class' => 'form-control address-state',
            ]);
            echo $form->field($address, 'country')->textInput([
                'class' => 'form-control address-country',
            ]);
            echo $form->field($address, 'contact')->textInput([
                'class' => 'form-control address-contact',
            ]);
            echo $form->field($address, 'phone')->textInput([
                'class' => 'form-control address-phone',
            ]);
            echo $form->field($address, 'instructions')->textInput([
                'class' => 'form-control address-instructions',
            ])
                ->hint(Yii::t('app', 'EG: delivered tuesday 26/09 by 3pm, authority to leave, etc'))
                ->label(Yii::t('app', 'Delivery Instructions'));
            echo '</div>';
            $this->render('/postcode/_ajax_lookup_script', ['formType' => $form->type, 'label' => false]);
            ?>

        </div>
    </div>


    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($address->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $address->formName(),
        'class' => 'btn btn-success',
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
