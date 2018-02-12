<?php
use app\components\PrintSpool;
use app\components\ReturnUrl;
use app\models\Company;
use app\models\ItemType;
use dosamigos\tinymce\TinyMce;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemStatusForm $model
 */

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Item Status'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $statusDropDownData = $model->item->getStatusDropDownData(false);
        $form = ActiveForm::begin([
            'id' => 'Item',
            //'formConfig' => ['labelSpan' => 0],
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'enableClientValidation' => false,
            'action' => ['status', 'id' => $model->item->id],
            'options' => ['enctype' => 'multipart/form-data'],
            'encodeErrorSummary' => false,
        ]);
        echo Html::hiddenInput('ru', $ru);
        echo $model->errorSummary($form);

        //echo $form->field($model->item, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']])->label(false);
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
        ?>

        <?php if (in_array($model->item->item_type_id, [ItemType::ITEM_TYPE_EM_PRINT, ItemType::ITEM_TYPE_EM_HARDWARE])) { ?>
            <div id="item-supplier">
                <?php
                echo $form->field($model->item, 'supplier_id')->widget(Select2::className(), [
                    'model' => $model->item,
                    'attribute' => 'supplier_id',
                    'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->item->supplier_id])->all(), 'id', 'name'),
                    'options' => [
                        'multiple' => false,
                        'theme' => 'krajee',
                        'placeholder' => '',
                        'language' => 'en-US',
                        'width' => '100%',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
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
                ]);
                echo $form->field($model->item, 'purchase_order')->textInput();
                echo $form->field($model->item, 'supply_date')->widget(DatePicker::className(), [
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
        <?php } ?>

        <?php if (in_array($model->item->item_type_id, [ItemType::ITEM_TYPE_PRINT, ItemType::ITEM_TYPE_EM_PRINT])) { ?>
            <div id="item-artwork-notes" style="display:none;">
                <?php
                echo $form->field($model->item, 'artwork_notes')->textarea();
                ?>
            </div>
        <?php } ?>

        <?php if ($model->itemToMachine) { ?>
            <div id="item-machine" style="display:none;">
                <?php
                echo $form->field($model->itemToMachine, 'machine_id')->dropDownList($model->optsMachine(), ['prompt' => '']);
                echo $form->field($model->itemToMachine, 'details')->textarea();
                ?>
            </div>
        <?php } ?>

        <div id="item-artwork" style="display:none;">
            <?php
            echo $form->field($model->artwork, 'upload')->widget(FileInput::className(), [
                'options' => [
                    'name' => 'Artwork[upload]',
                    'accept' => 'image/*',
                ],
                'pluginOptions' => [
                    'showPreview' => true,
                    'showCaption' => false,
                    'showRemove' => true,
                    'showUpload' => false,
                ],
            ]);
            ?>
        </div>

        <div id="item-change">
            <?php
            echo $form->field($model->item, 'change_requested_by')->textInput();
            echo $form->field($model->item, 'change_request_details')->textarea();
            ?>
        </div>

        <div id="item-print">
            <?php
            echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
            echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
            ?>
        </div>

        <div id="item-status-change">
            <div id="item-send-email-wrapper" style="display:none;">
                <?php
                echo $form->field($model->item, 'send_email')->checkbox();
                ?>
                <div id="item-send-email-details" style="display:none;">
                    <?php
                    echo $this->render('/job/_artwork-email-details', ['model' => $model->item->product->job]);
                    echo $form->field($model, 'artwork_email_text')->widget(TinyMce::className(), [
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
                    ])
                        ->hint(Yii::t('app', 'The {contact_first_name}, {job_label}, {approval_button}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.'))
                        ->label(false);
                    ?>
                </div>
            </div>

            <?php
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['/job/view', 'id' => $model->item->product->job->id]), ['class' => 'btn btn-default']);
            ?>
        </div>
        <?php

        ActiveForm::end();
        ?>
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            var $status = $('#itemstatusform-new_status'),
                $itemSendEmail = $('#item-send_email'),
                $sendEmail = $('#item-send-email-wrapper'),
                $sendEmailDetails = $('#item-send-email-details'),
                $artworkNotes = $('#item-artwork-notes'),
                $machine = $('#item-machine'),
                $artwork = $('#item-artwork'),
                $itemPrint = $('.field-itemstatusform-print'),
                $itemChange = $('#item-change'),
                $itemPrintSpool = $('.field-itemstatusform-print_spool'),
                oldStatus = '<?=$model->old_status?>'.split('/')[1];
            $status.change(function () {
                var status = $status.val().split('/')[1];
                $artworkNotes.hide();
                if (status === 'design' || status === 'artwork') {
                    $artworkNotes.show();
                }
                $machine.hide();
                if (status === 'rip' || status === 'production') {
                    $machine.show();
                }
                $artwork.hide();
                if (status === 'approval') {
                    $artwork.show();
                }
                $itemChange.hide();
                if (status === 'change') {
                    $itemChange.show();
                }
                $sendEmail.hide();
                if (status === 'approval') {
                    $sendEmail.show();
                }
            });
            $itemPrintSpool.hide();
            $itemPrint.find(':input').change(function () {
                if ($itemPrint.find(':input:checked').length > 0) {
                    $itemPrintSpool.show();
                } else {
                    $itemPrintSpool.hide();
                }
            });
            $itemSendEmail.change(function () {
                if ($(this).is(':checked')) {
                    $sendEmailDetails.show();
                } else {
                    $sendEmailDetails.hide();
                }
            }).change();
            $status.change();
        </script>
        <?php \app\widgets\JavaScript::end() ?>
    </div>
</div>

