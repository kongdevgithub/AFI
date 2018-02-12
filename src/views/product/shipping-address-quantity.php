<?php

use app\models\Address;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductShippingAddressQuantityForm $model
 * @var ActiveForm $form
 */

$this->title = $model->product->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="product-shipping-address-quantity">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Shipping Address'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Product',
                'type' => 'horizontal',
                'formConfig' => ['labelSpan' => 10],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $model->errorSummary($form);

            //echo $form->field($model, 'quote_factor')->textInput();

            foreach ($model->productToAddresses as $productToAddress) {
                echo $form->field($productToAddress, 'quantity')->textInput([
                    'id' => 'ProductToAddresses_' . $productToAddress->address_id . '_quantity',
                    'name' => 'ProductToAddresses[' . $productToAddress->address_id . '][quantity]',
                ])->label($productToAddress->address->getLabel());
                echo Html::hiddenInput('ProductToAddresses[' . $productToAddress->address_id . '][address_id]', $productToAddress->address_id, [
                    'id' => 'ProductToAddresses_' . $productToAddress->address_id . '_quantity',
                ]);
            }


            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>

    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

