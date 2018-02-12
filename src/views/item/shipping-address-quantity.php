<?php

use app\models\Address;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemShippingAddressQuantityForm $model
 * @var ActiveForm $form
 */

$this->title = $model->item->product->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->item->product->job->vid . ': ' . $model->item->product->job->name, 'url' => ['/job/view', 'id' => $model->item->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->item->product->id . ': ' . $model->item->product->name, 'url' => ['/product/view', 'id' => $model->item->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->item->id . ': ' . $model->item->name, 'url' => ['/item/view', 'id' => $model->item->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="item-shipping-address-quantity">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Shipping Address'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Item',
                'type' => 'horizontal',
                'formConfig' => ['labelSpan' => 10],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $model->errorSummary($form);

            //echo $form->field($model, 'quote_factor')->textInput();

            foreach ($model->itemToAddresses as $itemToAddress) {
                echo $form->field($itemToAddress, 'quantity')->textInput([
                    'id' => 'ItemToAddresses_' . $itemToAddress->address_id . '_quantity',
                    'name' => 'ItemToAddresses[' . $itemToAddress->address_id . '][quantity]',
                ])->label($itemToAddress->address->name);
                echo Html::hiddenInput('ItemToAddresses[' . $itemToAddress->address_id . '][address_id]', $itemToAddress->address_id, [
                    'id' => 'ItemToAddresses_' . $itemToAddress->address_id . '_quantity',
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

