<?php

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Billing Address');
?>

<div class="job-billing-address">

    <?php $form = ActiveForm::begin([
        'id' => 'Address',
        'type' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model->billingAddress); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Billing Address'); ?></h3>
        </div>
        <div class="box-body">

            <?php
            $items = ArrayHelper::map($model->company->addresses, 'id', 'label');
            echo $form->field($model->billingAddress, 'label')->dropDownList($items, [
                'prompt' => '',
            ])->label(Yii::t('app', 'Lookup'));
            ?>

            <?php
            echo '<div class="address">';
            echo $form->field($model->billingAddress, 'name')->textInput([
                'class' => 'form-control address-name',
            ]);
            echo $form->field($model->billingAddress, 'street')->textarea([
                'class' => 'form-control address-street',
            ]);
            echo $form->field($model->billingAddress, 'postcode')->textInput([
                'class' => 'form-control address-postcode',
            ]);
            echo $form->field($model->billingAddress, 'city')->textInput([
                'class' => 'form-control address-city',
            ]);
            echo $form->field($model->billingAddress, 'state')->textInput([
                'class' => 'form-control address-state',
            ]);
            echo $form->field($model->billingAddress, 'country')->textInput([
                'class' => 'form-control address-country',
            ]);
            echo $form->field($model->billingAddress, 'contact')->textInput([
                'class' => 'form-control address-contact',
            ]);
            echo $form->field($model->billingAddress, 'phone')->textInput([
                'class' => 'form-control address-phone',
            ]);
            //echo $form->field($model->billingAddress, 'instructions')->textInput([
            //    'class' => 'form-control address-instructions',
            //]);
            echo '</div>';
            $this->render('//postcode/_ajax_lookup_script', ['formType' => $form->type, 'label' => false]);
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

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->billingAddress->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->billingAddress->formName(),
        'class' => 'btn btn-success',
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
