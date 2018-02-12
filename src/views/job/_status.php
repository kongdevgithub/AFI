<?php
use app\components\Helper;
use app\components\ReturnUrl;
use dosamigos\tinymce\TinyMce;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$action = isset($action) ? $action : ['status', 'id' => $model->id];
$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Job Status'); ?></h3>
        <div class="box-tools pull-right text-right">
            <?= $model->getIcons() ?>
        </div>
    </div>
    <div class="box-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'Job',
            'formConfig' => ['labelSpan' => 0],
            'enableClientValidation' => false,
            'action' => $action,
            'encodeErrorSummary' => false,
            'fieldConfig' => [
                'errorOptions' => [
                    'encode' => false,
                    'class' => 'help-block',
                ],
            ],
        ]);
        echo Html::hiddenInput('ru', $ru);
        echo $form->errorSummary($model);

        $statusDropDownData = $model->getStatusDropDownData(false);
        echo $form->field($model, 'status')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'status',
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
        ])->label(false);

        ?>

        <div id="job-status-quoteLost" style="display:none;">
            <?php
            echo $form->field($model, 'quote_lost_reason')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'quote_lost_reason',
                'data' => $model::optsQuoteLostReason(),
                'pluginOptions' => [
                    'placeholder' => '',
                    'allowClear' => true,
                    'tags' => true,
                ],
            ]);
            ?>
        </div>

        <div id="job-due" style="display:none;">
            <?php
            echo $form->field($model, 'production_days')->textInput();
            echo $form->field($model, 'prebuild_days')->textInput();
            echo $form->field($model, 'freight_days')->textInput();
            echo $form->field($model, 'due_date')->widget(DatePicker::className(), [
                'layout' => '{picker}{input}',
                'options' => [
                    'class' => 'form-control',
                    'id' => 'Job-due-status',
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd',
                    'orientation' => 'top left',
                ],
            ]);
            ?>
        </div>

        <div id="job-early-due" style="display:none;">
            <?php
            if (strtotime($model->due_date) < strtotime('+7days')) {
                echo $form->field($model, 'allow_early_due')->checkbox();
            }
            ?>
        </div>
        <?php
        if (Yii::$app->user->can('_allow_excessive_discount')) {
            ?>
            <div id="job-allow-excessive-discount" style="display:none;">
                <?php
                $discount = $model->getExcessiveDiscount();
                if ($discount > 0) {
                    echo $form->field($model, 'allow_excessive_discount')->checkbox(['label' => Yii::t('app', 'Allow Excessive Discount of {amount}', [
                        'amount' => '$' . number_format($discount, 2),
                    ])]);
                }
                ?>
            </div>
            <?php
        }
        ?>

        <div id="send-email" style="display:none;">
            <?php
            echo $form->field($model, 'send_email')->checkbox();
            ?>
            <div id="send-email-details" style="display:none;">
                <?php
                echo $this->render('_quote-email-details', ['model' => $model]);
                echo $form->field($model, 'quote_email_text')->widget(TinyMce::className(), [
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
                    ->hint(Yii::t('app', 'The {contact_first_name}, {quote_label}, {approval_button}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.'))
                    ->label(false);
                ?>
            </div>
        </div>
        <?php
        echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
            'id' => 'save-' . $model->formName(),
            'class' => 'btn btn-success'
        ]);
        echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']);
        ?>

        <?php
        ActiveForm::end();
        ?>
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            var $status = $('#job-status'),
                $statusQuoteLost = $('#job-status-quoteLost'),
                $jobAllowExcessiveDiscount = $('#job-allow-excessive-discount'),
                $jobIgnoreProductStatus = $('#job-ignore-product-status'),
                $jobDue = $('#job-due'),
                $jobEarlyDue = $('#job-early-due'),
                $jobSendEmail = $('#job-send_email'),
                $sendEmail = $('#send-email'),
                $sendEmailDetails = $('#send-email-details'),
                oldStatus = '<?=$model->getOldAttribute('status')?>';
            $status.change(function () {
                var status = $status.val();
                $jobAllowExcessiveDiscount.hide();
                if (status === 'job/quote' || status === 'job/production') {
                    $jobAllowExcessiveDiscount.show();
                }
                $jobIgnoreProductStatus.hide();
                if (status === 'job/complete') {
                    $jobIgnoreProductStatus.show();
                }
                $statusQuoteLost.hide();
                if (status === 'job/quoteLost') {
                    $statusQuoteLost.show();
                }
                $jobDue.hide();
                if (status === 'job/productionPending' || status === 'job/production') {
                    $jobDue.show();
                }
                $jobEarlyDue.hide();
                if (status === 'job/production') {
                    $jobEarlyDue.show();
                }
                $sendEmail.hide();
                if (status === 'job/quote') {
                    $sendEmail.show();
                }
            });
            $jobSendEmail.change(function () {
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

