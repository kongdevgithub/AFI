<?php

/**
 * @var yii\web\View $this
 * @var array $ids
 * @var app\models\form\PackageAddressForm $model
 */
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Assign Address to Packages');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['package/index']];
//$this->params['breadcrumbs'][] = ['label' => 'package-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="package-address">

    <?php
    $form = ActiveForm::begin([
        'id' => 'PackageAddress',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'action' => ['address', 'confirm' => true],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $model->errorSummary($form);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    foreach ($model->ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    ?>

    <div class="address">
        <?php
        echo Html::hiddenInput('Address[type]', 'shipping');
        echo $form->field($model->address, 'label')->dropDownList($model->optsAddress(), [
            'prompt' => '',
        ])->label(Yii::t('app', 'Lookup'));
        echo $form->field($model->address, 'name')->textInput([
            'class' => 'form-control address-name',
        ]);
        echo $form->field($model->address, 'street')->textarea([
            'class' => 'form-control address-street',
        ]);
        echo $form->field($model->address, 'postcode')->textInput([
            'class' => 'form-control address-postcode',
        ]);
        echo $form->field($model->address, 'city')->textInput([
            'class' => 'form-control address-city',
        ]);
        echo $form->field($model->address, 'state')->textInput([
            'class' => 'form-control address-state',
        ]);
        echo $form->field($model->address, 'country')->textInput([
            'class' => 'form-control address-country',
        ]);
        echo $form->field($model->address, 'contact')->textInput([
            'class' => 'form-control address-contact',
        ]);
        echo $form->field($model->address, 'phone')->textInput([
            'class' => 'form-control address-phone',
        ]);
        echo $form->field($model->address, 'instructions')->textInput([
            'class' => 'form-control address-instructions',
        ])
            ->hint(Yii::t('app', 'EG: delivered tuesday 26/09 by 3pm, authority to leave, etc'))
            ->label(Yii::t('app', 'Delivery Instructions'));
        $this->render('/postcode/_ajax_lookup_script', ['formType' => $form->type, 'label' => false]);
        ?>
    </div>
    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

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
                        $('.address-contact').last().val(address.contact);
                        $('.address-phone').last().val(address.phone);
                        $('.address-instructions').last().val(address.instructions);
                    });
                }
            });
        }
    </script>
    <?php \app\widgets\JavaScript::end() ?>
</div>

