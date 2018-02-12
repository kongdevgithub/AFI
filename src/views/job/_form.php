<?php

use app\components\quotes\jobs\BaseJobQuote;
use app\models\AccountTerm;
use app\models\Company;
use app\models\Job;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\models\User;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use dosamigos\tinymce\TinyMce;
use kartik\date\DatePicker;
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
            <?php if ($model->scenario == 'redo') { ?>
                <?= $form->field($model, 'redo_reason')->textarea() ?>
            <?php } ?>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model->job, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'company_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'company_id',
                        'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->job->company_id])->all(), 'id', 'name'),
                        'pluginEvents' => [
                            'select2:select' => "function(e) { populateCompanyData(e.params.data.id); }",
                        ],
                        'pluginOptions' => [
                            'minimumInputLength' => 2,
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
                            populateRollout(company_id);
                            populateContact(company_id);
                            //populateAddresses(company_id);
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
                        'pluginOptions' => [
                            //'ajax' => [
                            //    'url' => Url::to(['contact/json-list']),
                            //    'dataType' => 'json',
                            //    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            //],
                        ],
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'job_type_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'job_type_id',
                        'data' => ArrayHelper::map(JobType::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'quote_win_chance')->dropDownList(Job::optsQuoteWinChance(), [
                        'prompt' => '',
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'purchase_order')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?php
                    $users = ArrayHelper::map(User::find()
                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('rep')])
                        ->orWhere(['id' => $model->job->staff_rep_id])
                        ->orderBy('username')->all(), 'id', 'label');
                    echo $form->field($model->job, 'staff_rep_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'staff_rep_id',
                        'data' => $users,
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <?php
                    $users = ArrayHelper::map(User::find()
                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('csr')])
                        ->orWhere(['id' => $model->job->staff_csr_id])
                        ->orderBy('username')->all(), 'id', 'label');
                    echo $form->field($model->job, 'staff_csr_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'staff_csr_id',
                        'data' => $users,
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <?php
                    $users = ArrayHelper::map(User::find()
                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('csr')])
                        ->orWhere(['id' => $model->job->staff_designer_id])
                        ->orderBy('username')->all(), 'id', 'label');
                    echo $form->field($model->job, 'staff_designer_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'staff_designer_id',
                        'data' => $users,
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model->job, 'rollout_id')->widget(Select2::className(), [
                        'model' => $model->job,
                        'attribute' => 'rollout_id',
                        'data' => ArrayHelper::map(Rollout::find()->andWhere(['company_id' => $model->job->company_id])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
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
                                echo $form->field($model, "preserve_unit_prices[$product->id]")->checkbox()
                                    ->hint(Yii::t('app', 'Check to lock the unit price of this product.'));
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

    <?php if (in_array($model->scenario, ['copy', 'redo'])) { ?>
        <div class="box collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Attachments and Notes'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?= $this->render('_form-copy', [
                    'model' => $model,
                    'form' => $form,
                    'modelCopy' => $modelCopy,
                ]) ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->job->status == 'job/draft') { ?>
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Quote Settings'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model->job, 'quote_email_text')->widget(TinyMce::className(), [
                            'options' => ['rows' => 8],
                            'clientOptions' => [
                                'menubar' => false,
                                'toolbar' => 'styleselect | bold italic | bullist numlist outdent indent | code',
                                'style_formats' => [
                                    ['title' => 'heading', 'block' => 'h3'],
                                    ['title' => 'lead', 'block' => 'p', 'styles' => ['font-size' => '17px', 'padding' => '', 'background-color' => '']],
                                    ['title' => 'paragraph', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '', 'background-color' => '']],
                                    ['title' => 'callout', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '15px', 'background-color' => '#ecf8ff']],
                                    ['title' => 'link', 'inline' => 'a', 'styles' => ['color' => '#2ba6cb', 'font-weight' => 'bold']],
                                ],
                                'plugins' => [
                                    'advlist autolink lists link charmap print preview anchor',
                                    'searchreplace visualblocks code fullscreen',
                                    'insertdatetime media table contextmenu paste',
                                ],
                            ]
                        ])->hint(Yii::t('app', 'The {contact_first_name}, {quote_label}, {approval_button}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model->job, 'quote_greeting_text')->textarea([
                            'rows' => 6,
                        ])->hint(Yii::t('app', 'The {contact_first_name}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                        <?= $form->field($model->job, 'quote_footer_text')->textarea([
                            'rows' => 6,
                        ]) ?>
                        <?= $form->field($model->job, 'quote_template')->dropDownList(Job::optsQuoteTemplate(), [
                            'prompt' => '',
                        ]) ?>
                        <?= $form->field($model->job, 'quote_totals_format')->dropDownList(Job::optsQuoteTotalsFormat(), [
                            'prompt' => Yii::t('app', 'Show Totals and Product Prices'),
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="box box-default collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Artwork Approval Settings'); ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model->job, 'artwork_email_text')->widget(TinyMce::className(), [
                        'options' => ['rows' => 8],
                        'clientOptions' => [
                            'menubar' => false,
                            'toolbar' => 'styleselect | bold italic | bullist numlist outdent indent | code',
                            'style_formats' => [
                                ['title' => 'heading', 'block' => 'h3'],
                                ['title' => 'lead', 'block' => 'p', 'styles' => ['font-size' => '17px', 'padding' => '', 'background-color' => '']],
                                ['title' => 'paragraph', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '', 'background-color' => '']],
                                ['title' => 'callout', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '15px', 'background-color' => '#ecf8ff']],
                                ['title' => 'link', 'inline' => 'a', 'styles' => ['color' => '#2ba6cb', 'font-weight' => 'bold']],
                            ],
                            'plugins' => [
                                'advlist autolink lists link charmap print preview anchor',
                                'searchreplace visualblocks code fullscreen',
                                'insertdatetime media table contextmenu paste',
                            ],
                        ]
                    ])->hint(Yii::t('app', 'The {contact_first_name}, {job_label}, {approval_button}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model->job, 'artwork_greeting_text')->textarea([
                        'rows' => 6,
                    ])->hint(Yii::t('app', 'The {contact_first_name}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (Y::user()->can('finance')) { ?>

        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Invoice Settings'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model->job, 'invoice_email_text')->widget(TinyMce::className(), [
                            'options' => ['rows' => 8],
                            'clientOptions' => [
                                'menubar' => false,
                                'toolbar' => 'styleselect | bold italic | bullist numlist outdent indent | code',
                                'style_formats' => [
                                    ['title' => 'heading', 'block' => 'h3'],
                                    ['title' => 'lead', 'block' => 'p', 'styles' => ['font-size' => '17px', 'padding' => '', 'background-color' => '']],
                                    ['title' => 'paragraph', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '', 'background-color' => '']],
                                    ['title' => 'callout', 'block' => 'p', 'styles' => ['font-size' => '', 'padding' => '15px', 'background-color' => '#ecf8ff']],
                                    ['title' => 'link', 'inline' => 'a', 'styles' => ['color' => '#2ba6cb', 'font-weight' => 'bold']],
                                ],
                                'plugins' => [
                                    'advlist autolink lists link charmap print preview anchor',
                                    'searchreplace visualblocks code fullscreen',
                                    'insertdatetime media table contextmenu paste',
                                ],
                            ]
                        ])->hint(Yii::t('app', 'The {contact_first_name}, {job_label}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model->job, 'invoice_greeting_text')->textarea([
                            'rows' => 6,
                        ])->hint(Yii::t('app', 'The {contact_first_name}, {due_date}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="box collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Finance Settings'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">

                <div class="row">
                    <?php
                    if (Y::user()->can('_update_account_term')) {
                        ?>
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'account_term_id')->widget(Select2::className(), [
                                'model' => $model->job,
                                'attribute' => 'account_terms_id',
                                'data' => ArrayHelper::map(AccountTerm::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                            ]);
                            ?>
                        </div>
                        <?php
                    }
                    if (Y::user()->can('_update_price_structure')) {
                        ?>
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'price_structure_id')->widget(Select2::className(), [
                                'model' => $model->job,
                                'attribute' => 'price_structure_id',
                                'data' => ArrayHelper::map(PriceStructure::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                            ]);
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-md-2">
                        <?php
                        echo $form->field($model->job, 'excludes_tax')->checkbox();
                        ?>
                    </div>
                </div>

                <?php
                if (in_array($model->job->status, ['job/production', 'job/despatch', 'job/complete'])) {
                    ?>
                    <div class="row">
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'invoice_sent')->widget(DatePicker::className(), [
                                'layout' => '{picker}{input}',
                                'options' => ['class' => 'form-control'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'todayHighlight' => true,
                                    'format' => 'yyyy-mm-dd',
                                    //'orientation' => 'top left',
                                ],
                            ]);
                            ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'invoice_amount')->textInput()->hint(Yii::t('app', 'Quoted {total} ex GST', [
                                'total' => '$' . number_format($model->job->quote_total_price - $model->job->quote_tax_price, 2),
                            ]));
                            ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'invoice_reference')->textInput();
                            ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                            echo $form->field($model->job, 'invoice_paid')->widget(DatePicker::className(), [
                                'layout' => '{picker}{input}',
                                'options' => ['class' => 'form-control'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'todayHighlight' => true,
                                    'format' => 'yyyy-mm-dd',
                                    //'orientation' => 'top left',
                                ],
                            ]);
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>

    <?php } ?>

    <?php if (Yii::$app->user->can('manager')) { ?>
        <div class="box collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Manager Settings'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <?php
                        $users = ArrayHelper::map(User::find()
                            ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('rep')])
                            ->orWhere(['id' => $model->job->staff_lead_id])
                            ->orderBy('username')->all(), 'id', 'label');
                        echo $form->field($model->job, 'staff_lead_id')->widget(Select2::className(), [
                            'model' => $model->job,
                            'attribute' => 'staff_lead_id',
                            'data' => $users,
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->job->status == 'job/draft' && Yii::$app->user->can('admin')) { ?>
        <div class="box collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Admin Settings'); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model->job, 'quote_class')->dropDownList(BaseJobQuote::opts(), [
                            'prompt' => '',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->job->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->job->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

