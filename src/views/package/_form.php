<?php

use app\models\PackageType;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;


/**
 * @var yii\web\View $this
 * @var app\models\form\PackageForm $model
 */
?>

<div class="package-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Package',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $model->errorSummary($form); ?>

    <div class="row">
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Package Details'); ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    echo $form->field($model->package, 'pickup_id')->textInput();;
                    //echo $form->field($model->package, 'cartons')->textInput();
                    if ($model->scenario == 'overflow') {
                        echo $form->field($model, 'quantity')->textInput();
                    }
                    ?>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Size'); ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    $items = ArrayHelper::map(PackageType::find()->all(), 'id', 'label');
                    echo $form->field($model->package, 'package_type_id')->dropDownList($items, [
                        'prompt' => '',
                    ])->label(Yii::t('app', 'Lookup'));
                    echo $form->field($model->package, 'type')->textInput();
                    echo $form->field($model->package, 'width')->textInput();
                    echo $form->field($model->package, 'length')->textInput();
                    echo $form->field($model->package, 'height')->textInput();
                    echo $form->field($model->package, 'dead_weight')->textInput();
                    ?>
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Address'); ?></h3>
                </div>
                <div class="box-body">
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
                </div>
            </div>

        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->package->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?= Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

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
                        var cityInput = "<?= addslashes(str_replace("\n", '', $form->field($model->address, 'city')->textInput(['class' => 'form-control address-city']))) ?>";
                        $('#address-city').parent().html($(cityInput).find(':input'));
                        //$('#address-city').parent().replaceWith(cityInput);

                        $('#address-name').val(address.name);
                        $('#address-street').val(address.street);
                        $('#address-city').val(address.city);
                        $('#address-state').val(address.state);
                        $('#address-country').val(address.country);
                        $('#address-postcode').val(address.postcode).change();
                    });
                }
            });
        }
        $('#package-package_type_id').change(function () {
            populatePackageDimensions($(this).val());
        });
        function populatePackageDimensions(package_type_id) {
            var url = '<?= Url::to(['package-type/json-list', 'PackageTypeSearch' => ['id' => '-package_type_id-']]) ?>';
            $.ajax({
                url: url.replace('-package_type_id-', package_type_id),
                success: function (data) {
                    data.forEach(function (packageType) {
                        $('#package-type').val(packageType.type);
                        $('#package-width').val(packageType.width);
                        $('#package-length').val(packageType.length);
                        $('#package-height').val(packageType.height);
                        $('#package-dead_weight').val(packageType.dead_weight);
                    });
                }
            });
        }
    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>
