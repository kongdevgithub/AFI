<?php

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var app\models\Address $address
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Shipping Address');
?>

<div class="job-shipping-address">

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
            $items = [];
            if ($model->billingAddress) {
                $items[$model->billingAddress->id] = Yii::t('app', 'Use Billing Address');
            }
            $items = ArrayHelper::merge($items, ArrayHelper::map($model->company->addresses, 'id', 'label'));
            echo $form->field($address, 'label')->widget(Select2::className(), [
                'model' => $address,
                'attribute' => 'rollout_id',
                'data' => $items,
                'options' => [
                    'multiple' => false,
                    'theme' => 'krajee',
                    'placeholder' => '',
                    'language' => 'en-US',
                    'width' => '100%',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(Yii::t('app', 'Lookup'));
            //echo $form->field($address, 'label')->dropDownList($items, [
            //    'prompt' => '',
            //])->label(Yii::t('app', 'Lookup'));
            ?>

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

            <?php \app\widgets\JavaScript::begin() ?>
            <script>
                $('#address-label').change(function () {
                    populateAddresses($(this).val());
                    $(this).val('');
                });
                function populateAddresses(address_id) {
                    var url = '<?= Url::to(['address/json-list', 'AddressSearch' => ['id' => '-address_id-']]) ?>';
                    $.ajax({
                        url: url.replace('-address_id-', address_id),
                        success: function (data) {
                            data.forEach(function (address) {
                                var $postcodeInput = $('.address-postcode').last(),
                                    $cityInput = $('.address-city').last();
                                $('.address-name').last().val(address.name);
                                $('.address-street').last().val(address.street);
                                $postcodeInput.val(address.postcode);
                                if ($cityInput.prop('tagName') === 'SELECT') {
                                    $cityInput.append(new Option(address.city, address.city));
                                }
                                $cityInput.val(address.city);
                                $('.address-state').last().val(address.state);
                                $('.address-country').last().val(address.country);
                                $('.address-contact').last().val(address.contact ? address.contact : '<?= Html::encode($model->contact->label)?>');
                                $('.address-phone').last().val(address.phone ? address.phone : '<?= Html::encode($model->contact->phone)?>');
                                $('.address-instructions').last().val(address.instructions);
                                $postcodeInput.change();
                            });
                        }
                    });
                }
            </script>
            <?php \app\widgets\JavaScript::end() ?>

        </div>
    </div>


    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($address->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $address->formName(),
        'class' => 'btn btn-success',
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
