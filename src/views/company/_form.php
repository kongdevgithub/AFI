<?php

use app\models\AccountTerm;
use app\models\Contact;
use app\models\form\CompanyForm;
use app\models\Industry;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\User;
use dosamigos\tinymce\TinyMce;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var CompanyForm $model
 * @var ActiveForm $form
 */

$model->no_website = strpos($model->company->website, 'example.com') === false;

?>

<div class="company-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Company',
        'type' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $model->errorSummary($form); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Company Details'); ?></h3>
        </div>
        <div class="box-body">

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model->company, 'name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model->company, 'phone')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model->company, 'fax')->textInput(['maxlength' => true]) ?>
                    <div id="website-field"<?= $model->no_website ? ' style="display:none;"' : '' ?>>
                        <?= $form->field($model->company, 'website')->textInput(['maxlength' => true]) ?>
                    </div>
                    <?= $form->field($model, 'no_website')->checkbox() ?>
                    <?php \app\widgets\JavaScript::begin(['position' => View::POS_END]) ?>
                    <script>
                        var companyWebsite = $('#company-website'),
                            oldWebsite,
                            noWebsite;
                        $('#companyform-no_website').change(function () {
                            if ($(this).is(':checked')) {
                                oldWebsite = companyWebsite.val();
                                companyWebsite.val(noWebsite ? noWebsite : 'example.com/' + getUniqueId());
                                $('#website-field').hide();
                            } else {
                                noWebsite = companyWebsite.val();
                                companyWebsite.val(oldWebsite);
                                $('#website-field').show();
                            }
                        });
                        function getUniqueId() {
                            var date = new Date();
                            var components = [
                                date.getYear(),
                                date.getMonth(),
                                date.getDate(),
                                date.getHours(),
                                date.getMinutes(),
                                date.getSeconds(),
                                date.getMilliseconds()
                            ];
                            return components.join('');
                        }
                    </script>
                    <?php \app\widgets\JavaScript::end() ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model->company, 'staff_rep_id')->widget(Select2::className(), [
                        'model' => $model->company,
                        'attribute' => 'staff_rep_id',
                        'data' => ArrayHelper::map(User::find()->orderBy(['username' => SORT_ASC])->all(), 'id', 'label'),
                        'options' => [
                            'placeholder' => '',
                            'multiple' => false,
                        ]
                    ]); ?>
                    <?= $form->field($model->company, 'default_contact_id')->widget(Select2::className(), [
                        'model' => $model->company,
                        'attribute' => 'default_contact_id',
                        'data' => ArrayHelper::map(Contact::find()->notDeleted()->joinWith('companies')->andWhere(['company.id' => $model->company->id])->orderBy(['first_name' => SORT_ASC, 'last_name' => SORT_ASC])->all(), 'id', 'label'),
                        'options' => [
                            'placeholder' => '',
                            'multiple' => false,
                        ]
                    ]); ?>
                    <?= $form->field($model->company, 'job_type_id')->widget(Select2::className(), [
                        'model' => $model->company,
                        'attribute' => 'job_type_id',
                        'data' => ArrayHelper::map(JobType::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                        'options' => [
                            'placeholder' => '',
                            'multiple' => false,
                        ]
                    ]); ?>
                    <?= $form->field($model->company, 'industry_id')->widget(Select2::className(), [
                        'model' => $model->company,
                        'attribute' => 'industry_id',
                        'data' => ArrayHelper::map(Industry::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                        'options' => [
                            'placeholder' => '',
                            'multiple' => false,
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (Yii::$app->user->can('despatch')) { ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Despatch Settings'); ?></h3>
            </div>
            <div class="box-body">
                <?= $form->field($model->company, 'delivery_docket_header')->widget(TinyMce::className(), [
                    'options' => ['rows' => 8],
                    'clientOptions' => [
                        'menubar' => false,
                        'toolbar' => 'bold italic',
                    ]
                ]) ?>
            </div>
        </div>
    <?php } ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Finance Settings'); ?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model->company, 'purchase_order_required')->checkbox(); ?>
                    <?= $form->field($model->company, 'excludes_tax')->checkbox(); ?>
                </div>
                <div class="col-md-6">
                    <?php if (Yii::$app->user->can('_update_account_term')) { ?>
                        <?= $form->field($model->company, 'account_term_id')->widget(Select2::className(), [
                            'model' => $model->company,
                            'attribute' => 'account_term_id',
                            'data' => ArrayHelper::map(AccountTerm::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                            'options' => [
                                'placeholder' => '',
                                'multiple' => false,
                            ]
                        ]); ?>
                    <?php } ?>
                    <?php if (Yii::$app->user->can('_update_price_structure')) { ?>
                        <?= $form->field($model->company, 'price_structure_id')->widget(Select2::className(), [
                            'model' => $model->company,
                            'attribute' => 'price_structure_id',
                            'data' => ArrayHelper::map(PriceStructure::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name'),
                            'options' => [
                                'placeholder' => '',
                                'multiple' => false,
                            ]
                        ]); ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (Yii::$app->user->can('manager')) { ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Manager Settings'); ?></h3>
            </div>
            <div class="box-body">
                <?= $form->field($model->company, 'rates_encoded')->textarea() ?>
            </div>
        </div>
    <?php } ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Address'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            echo '<div class="address">';
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
            echo '</div>';
            $this->render('/postcode/_ajax_lookup_script', ['formType' => $form->type, 'label' => false]);
            ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->company->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->company->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
