<?php

use app\components\PrintSpool;
use app\models\Job;
use app\models\Profile;
use app\models\User;
use dosamigos\tinymce\TinyMce;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \app\models\User $model
 */

$this->title = Yii::t('user', 'Application settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu') ?>
    </div>
    <div class="col-md-9">

        <?php
        $form = ActiveForm::begin([
            'id' => 'application-form',
        ]);
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'page_limit')->dropDownList(User::optsPageLimit(), ['prompt' => '']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool(), ['prompt' => '']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'job_view')->dropDownList(User::optsJobView(), ['prompt' => '']) ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Yii::t('app','Quote Settings') ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'quote_email_text')->widget(TinyMce::className(), [
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
                        <?= $form->field($model, 'quote_greeting_text')->textarea([
                            'rows' => 6,
                        ])->hint(Yii::t('app', 'The {contact_first_name}, {staff_rep_phone} and {staff_rep_name} will be replaced automatically.')) ?>
                        <?= $form->field($model, 'quote_footer_text')->textarea([
                            'rows' => 6,
                        ]) ?>
                        <?= $form->field($model, 'quote_template')->dropDownList(Job::optsQuoteTemplate(), [
                            'prompt' => '',
                        ]) ?>
                        <?= $form->field($model, 'quote_totals_format')->dropDownList(Job::optsQuoteTotalsFormat(), [
                            'prompt' => Yii::t('app', 'Show Totals and Product Prices'),
                        ]) ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Yii::t('app','Theme Settings') ?>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'skin')->dropDownList(User::optsSkin(), ['prompt' => '']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'background')->dropDownList(User::optsBackground(), ['prompt' => '']) ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-block btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
