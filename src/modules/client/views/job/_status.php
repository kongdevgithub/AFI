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

isset($action) || $action = ['status', 'id' => $model->id];
$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Job Status'); ?></h3>
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
            //echo $form->field($model, 'production_days')->textInput();
            //echo $form->field($model, 'prebuild_days')->textInput();
            //echo $form->field($model, 'freight_days')->textInput();
            $startDate = Helper::getRelativeDate(date('Y-m-d'), $model->production_days + $model->prebuild_days + $model->freight_days + 1);
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
                    'startDate' => $startDate,
                ],
            ])->hint(Yii::t('app', 'To set a date earlier than {date} please contact {contact} on {phone} or {email}.', [
                'date' => Yii::$app->formatter->asDate($startDate),
                'contact' => $model->staffRep->getLabel(),
                'phone' => $model->staffRep->profile->phone ? $model->staffRep->profile->phone : Yii::$app->settings->get('phone', 'app'),
                'email' => $model->staffRep->email,
            ]));
            ?>
        </div>

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
                $jobIgnoreProductStatus = $('#job-ignore-product-status'),
                $jobDue = $('#job-due'),
                $jobSendEmail = $('#job-send_email'),
                $sendEmail = $('#send-email'),
                $sendEmailDetails = $('#send-email-details'),
                oldStatus = '<?=$model->getOldAttribute('status')?>';
            $status.change(function () {
                var status = $status.val();
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

