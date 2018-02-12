<?php

use app\models\Company;
use app\models\Contact;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 * @var app\models\Job $modelCopy
 * @var ActiveForm $form
 */

$modelCopy = isset($modelCopy) ? $modelCopy : false;

$select2Options = [
    'multiple' => false,
    'theme' => 'krajee',
    'placeholder' => '',
    'language' => 'en-US',
    'width' => '100%',
];
?>

<div class="job-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Job',
        //'type' => 'horizontal',
        'formConfig' => [
            'labelSpan' => 0,
        ],
        'enableClientValidation' => false,
        //'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>
    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>
    <?= $model->errorSummary($form); ?>


    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Job Details'); ?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model->job, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'company_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'company_id',
                        'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->job->company_id])->all(), 'id', 'name'),
                        'options' => $select2Options,
                        'pluginEvents' => [
                            'select2:select' => "function(e) { populateCompanyData(e.params.data.id); }",
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            //'minimumInputLength' => 2,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                            ],
                            'ajax' => [
                                'url' => Url::to(['company/json-list']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ],
                    ]); ?>
                    <?php \app\widgets\JavaScript::begin(['position' => View::POS_END]) ?>
                    <script>
                        function populateCompanyData(company_id) {
                            populateContact(company_id);
                            var url = '<?= Url::to(['company/json-view', 'id' => '-company_id-']) ?>';
                            $.ajax({
                                url: url.replace('-company_id-', company_id),
                                success: function (data) {
                                    $('#job-staff_lead_id').val(data.staff_rep_id).trigger('change');
                                    $('#job-staff_rep_id').val(data.staff_rep_id).trigger('change');
                                    $('#job-price_structure_id').val(data.price_structure_id).trigger('change');
                                    $('#job-account_term_id').val(data.account_term_id).trigger('change');
                                    $('#job-job_type_id').val(data.job_type_id).trigger('change');
                                }
                            });
                        }
                        <?php
                        if ($model->job->isNewRecord && isset($_GET['Job']['company_id'])) {
                            echo 'populateCompanyData(' . $model->job->company_id . ');';
                        }
                        ?>
                        function populateContact(company_id) {
                            var url = '<?= Url::to(['contact/json-list', 'ContactSearch' => ['company_id' => '-company_id-']]) ?>';
                            var $select = $('#job-contact_id');
                            $select.find('option').remove().end();
                            $.ajax({
                                url: url.replace('-company_id-', company_id),
                                success: function (data) {
                                    $.each(data.results, function (i, item) {
                                        $select.append($('<option>', {value: item.id, text: item.text}));
                                    });
                                    $select.val(data.selected).trigger('change');
                                }
                            });
                        }
                        function populateRollout(company_id) {
                            var url = '<?= Url::to(['rollout/json-list', 'RolloutSearch' => ['company_id' => '-company_id-']]) ?>';
                            var $select = $('#job-rollout_id');
                            $select.find('option').remove().end();
                            $.ajax({
                                url: url.replace('-company_id-', company_id),
                                success: function (data) {
                                    $.each(data.results, function (i, item) {
                                        $select.append($('<option>', {value: item.id, text: item.text}));
                                    });
                                    $select.val(data.selected).trigger('change');
                                }
                            });
                        }
                        <?php /* ?>
                        function populateAddresses(company_id) {
                            var url = '<?= Url::to(['address/json-list', 'AddressSearch' => ['model_name' => Company::className(), 'model_id' => '-company_id-']]) ?>';
                            $.ajax({
                                url: url.replace('-company_id-', company_id),
                                success: function (data) {
                                    data.forEach(function (address) {
                                        $('#job-new-address-button').click();
                                        $('.address-type').last().val(address.type);
                                        $('.address-name').last().val(address.name);
                                        $('.address-street').last().val(address.street);
                                        $('.address-city').last().val(address.city);
                                        $('.address-state').last().val(address.state);
                                        $('.address-country').last().val(address.country);
                                        $('.address-postcode').last().val(address.postcode).change();
                                    });
                                }
                            });
                        }
                        <?php */ ?>
                    </script>
                    <?php \app\widgets\JavaScript::end() ?>
                </div>
                <div class="col-md-2">
                    <?php
                    $contacts = Contact::find()->notDeleted()->andWhere(['contact.id' => $model->job->contact_id]);
                    if ($model->job->company_id) {
                        $contacts->joinWith(['companies']);
                        $contacts->orWhere(['company.id' => $model->job->company_id]);
                    }
                    echo $form->field($model->job, 'contact_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'contact_id',
                        'data' => ArrayHelper::map($contacts->all(), 'id', 'labelWithEmail'),
                        //'data' => ArrayHelper::map(Contact::find()->andWhere(['company_id' => $model->job->company_id])->orderBy(['first_name' => SORT_ASC, 'last_name' => SORT_ASC])->all(), 'id', 'label'),
                        'options' => $select2Options,
                        'pluginOptions' => [
                            'allowClear' => true,
                            //'ajax' => [
                            //    'url' => Url::to(['contact/json-list']),
                            //    'dataType' => 'json',
                            //    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            //],
                        ],
                    ]); ?>
                </div>
            </div>

        </div>
    </div>

    <?php if ($model->scenario != 'create') { ?>
        <div class="box collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Quantities'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php
                $quantityModel = $modelCopy ?: $model->job;
                if ($quantityModel) {
                    foreach ($quantityModel->products as $product) {
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                echo $form->field($model, "products[$product->id]")->textInput()->label(implode(' | ', [
                                    'p' . $product->id,
                                    $product->name,
                                    $product->getSizeHtml(),
                                ]));
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                foreach ($product->items as $item) {
                                    ?>
                                    <div>
                                        <?php
                                        echo $form->field($model, "items[$item->id]")->textInput()->label(implode(' | ', [
                                            'i' . $item->id,
                                            $item->name,
                                            $item->getSizeHtml(),
                                        ]));
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->job->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->job->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

