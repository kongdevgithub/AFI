<?php
use app\components\PrintSpool;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\form\UnitStatusForm $model
 */

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Unit Status'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $statusDropDownData = $model->unit->getStatusDropDownData(false);
        $form = ActiveForm::begin([
            'id' => 'Unit',
            'type' => ActiveForm::TYPE_HORIZONTAL,
            //'formConfig' => ['labelSpan' => 0],
            'enableClientValidation' => false,
            'action' => ['status', 'id' => $model->unit->id],
            'encodeErrorSummary' => false,
        ]);
        echo Html::hiddenInput('ru', $ru);
        echo $model->errorSummary($form);
        ?>

        <?php
        //echo $form->field($model, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']])->label(false);
        echo $form->field($model, 'old_status')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'old_status',
            'data' => $statusDropDownData['items'],
            'options' => [
                'multiple' => false,
                'options' => $statusDropDownData['options'],
                'disabled' => true,
            ],
            'pluginOptions' => [
                'templateResult' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                'templateSelection' => new JsExpression("function(o) { console.log(o);return '<span class=\"' + $(o.element).data('icon') + '\"></span>&nbsp; &nbsp;' + o.text; }"),
                'escapeMarkup' => new JsExpression("function(m) { return m; }"),
            ],
        ]);

        echo $form->field($model, 'new_status')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'new_status',
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
        ]);
        echo $form->field($model->unit, 'quantity')->textInput();
        ?>

        <div id="unit-status-qualityFail" style="display:none;">
            <?php
            echo $form->field($model->unit, 'quality_fail_reason')->textInput();
            ?>
        </div>

        <div id="unit-print">
            <?php
            echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
            echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
            ?>
        </div>

        <?php if (!$model->unit->item->itemType->virtual) { ?>
            <div id="unit-package">
                <?php
                $packages = [];
                foreach ($model->unit->item->product->job->packages as $package) {
                    $packages[$package->id] = 'package-' . $package->id;
                }
                echo $form->field($model->unit, 'package_id')->dropDownList($packages, [
                    'prompt' => Yii::t('app', 'New Package'),
                ]);
                ?>
            </div>

            <div id="unit-package-new" style="display: none;">
                <?php
                //echo $form->field($model->package, 'cartons')->textInput();
                ?>
                <div id="unit-package-address" class="address">
                    <?php
                    echo Html::hiddenInput('Address[type]', 'shipping');
                    $items = ArrayHelper::map($model->unit->item->product->job->addresses, 'id', 'label');
                    echo $form->field($model->address, 'label')->dropDownList($items, [
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
        <?php } ?>

        <div id="unit-status-change">
            <?php
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->unit->formName(),
                'class' => 'btn btn-success'
            ]);
            echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['/job/view', 'id' => $model->unit->item->product->job->id]), ['class' => 'btn btn-default']);
            ?>
        </div>
        <?php
        ActiveForm::end();
        ?>

        <?php JavaScript::begin() ?>
        <script>
            var $status = $('#unitstatusform-new_status'),
                $statusQualityFail = $('#unit-status-qualityFail'),
                $package = $('#unit-package'),
                $packageId = $('#unit-package_id'),
                $packageNew = $('#unit-package-new'),
                $printPackageLabel = $('.field-unitstatusform-print_package_label'),
                $unitPrint = $('.field-unitstatusform-print'),
                $unitPrintSpool = $('.field-unitstatusform-print_spool'),
                oldStatus = '<?=$model->old_status?>'.split('/')[1];
            $status.change(function () {
                status = $status.val().split('/')[1];
                $statusQualityFail.hide();
                if (status === 'qualityFail') {
                    $statusQualityFail.show();
                }
                $package.hide();
                if (status === 'packed' || status === 'complete') {
                    $package.show();
                }
                $packageNew.hide();
                if ((status === 'packed' || status === 'complete') && $packageId.val() === '') {
                    $packageNew.show();
                }
                $printPackageLabel.hide();
                if (status === 'packed' || status === 'complete') {
                    $printPackageLabel.show();
                }
            });
            $unitPrintSpool.hide();
            $unitPrint.find(':input').change(function () {
                if ($unitPrint.find(':input:checked').length > 0) {
                    $unitPrintSpool.show();
                } else {
                    $unitPrintSpool.hide();
                }
            });
            $packageId.change(function () {
                $packageNew.hide();
                if ((status === 'packed' || status === 'complete') && $packageId.val() === '') {
                    $packageNew.show();
                }
            });
            $status.change();

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
                            $('.address-city').last().parent().html($(cityInput).find(':input'));
                            //$('.address-city').last().parent().replaceWith(cityInput);

                            $('.address-name').last().val(address.name);
                            $('.address-street').last().val(address.street);
                            $('.address-city').last().val(address.city);
                            $('.address-state').last().val(address.state);
                            $('.address-country').last().val(address.country);
                            $('.address-contact').last().val(address.contact);
                            $('.address-phone').last().val(address.phone);
                            $('.address-instructions').last().val(address.instructions);
                            $('.address-postcode').last().val(address.postcode).change();
                        });
                    }
                });
            }
        </script>
        <?php JavaScript::end() ?>
    </div>
</div>

