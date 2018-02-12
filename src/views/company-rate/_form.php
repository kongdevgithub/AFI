<?php

use app\models\Company;
use app\models\CompanyRateOption;
use app\models\Component;
use app\models\ItemType;
use app\models\Option;
use app\models\ProductType;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\select2\Select2Asset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 *
 * @var yii\web\View $this
 * @var app\models\form\CompanyRateForm $model
 * @var kartik\form\ActiveForm $form
 */
?>

<div class="company-rate-form">

    <?php $form = ActiveForm::begin([
        'id' => 'CompanyRate',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?php echo Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?php echo $model->errorSummary($form); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('app', 'Company Rate') ?></h3>
        </div>
        <div class="box-body">

            <div class="row">
                <div class="col-md-8">

                    <?= $form->field($model->companyRate, 'company_id')->widget(Select2::className(), [
                        'model' => $model->companyRate,
                        'attribute' => 'company_id',
                        'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->companyRate->company_id])->all(), 'id', 'name'),
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
                    ])->hint(Yii::t('app', 'The company who will receive the rate.')); ?>

                    <?= $form->field($model->companyRate, 'product_type_id')->widget(Select2::className(), [
                        'model' => $model->companyRate,
                        'attribute' => 'product_type_id',
                        'data' => ProductType::getDropdownOpts(),
                    ])->hint(Yii::t('app', 'The product type (or the parent product type) that the rate is restricted to.')); ?>

                    <?= $form->field($model->companyRate, 'item_type_id')->widget(Select2::className(), [
                        'model' => $model->companyRate,
                        'attribute' => 'item_type_id',
                        'data' => ArrayHelper::map(ItemType::find()->all(), 'id', 'name'),
                    ])->hint(Yii::t('app', 'The item type that the rate is restricted to.')); ?>

                    <?= $form->field($model->companyRate, 'size')->textInput()->hint(Yii::t('app', 'The size that rate is restricted to (leave empty for M2 rate).')); ?>

                    <?= $form->field($model->companyRate, 'option_id')->widget(Select2::className(), [
                        'model' => $model->companyRate,
                        'attribute' => 'option_id',
                        'data' => ArrayHelper::map(Option::find()->all(), 'id', 'name'),
                    ])->hint(Yii::t('app', 'The option to restrict to, will contain the Component, usually Substrate.')); ?>

                    <?= $form->field($model->companyRate, 'component_id')->widget(Select2::className(), [
                        'model' => $model->companyRate,
                        'attribute' => 'component_id',
                        'data' => ArrayHelper::map(Component::find()->andWhere(['id' => $model->companyRate->component_id])->all(), 'id', 'label'),
                        'pluginOptions' => [
                            'minimumInputLength' => 2,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                            ],
                            'ajax' => [
                                'url' => Url::to(['component/json-list']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ],
                    ])->hint(Yii::t('app', 'The component to restrict to, will be selected in the Option, usually the type of substrate.')); ?>

                    <?= $form->field($model->companyRate, 'price')->textInput(['maxlength' => true, 'type' => 'number', 'step' => 0.01])
                        ->hint(Yii::t('app', 'The retail price that will be charged for the item (enter the M2 prices when Size is empty).')); ?>

                </div>
                <div class="col-md-4">


                    <fieldset>
                        <legend><?= Yii::t('app', 'Required Options and Components') ?><?php
                            // new option button
                            echo Html::a('New Option', 'javascript:void(0);', [
                                'id' => 'company-rate-new-company-rate-option-button',
                                'class' => 'pull-right btn btn-default btn-xs'
                            ])
                            ?>
                        </legend>
                        <p>This section lists options that MUST be present in the item build in order to recieve the rates.</p>
                        <?php

                        // CompanyRateOption table
                        $companyRateOption = new CompanyRateOption();
                        $companyRateOption->loadDefaultValues();
                        echo '<table id="company-rate-company-rate-options" class="table table-condensed table-bordered">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . $companyRateOption->getAttributeLabel('option_id') . '</th>';
                        echo '<th>' . $companyRateOption->getAttributeLabel('component_id') . '</th>';
                        echo '<td>&nbsp;</td>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        // existing CompanyRateOption fields
                        foreach ($model->companyRateOptions as $key => $_companyRateOption) {
                            echo '<tr>';
                            echo $this->render('_form-company-rate-option', [
                                'key' => $_companyRateOption->isNewRecord ? (strpos($key, 'new') !== false ? $key : 'new' . $key) : $_companyRateOption->id,
                                'form' => $form,
                                'companyRateOption' => $_companyRateOption,
                            ]);
                            echo '</tr>';
                        }

                        // new CompanyRateOption fields
                        echo '<tr id="company-rate-new-company-rate-option-block" style="display: none;">';
                        echo $this->render('_form-company-rate-option', [
                            'key' => '__id__',
                            'form' => $form,
                            'companyRateOption' => $companyRateOption,
                        ]);
                        echo '</tr>';
                        echo '</tbody>';
                        echo '</table>';

                        Select2Asset::register($this);
                        ?>

                        <?php JavaScript::begin() ?>
                        <script>

                            // add CompanyRateOption button
                            var companyRateOption_k = <?php echo isset($key) ? str_replace('new', '', $key) : 0; ?>;
                            $('#company-rate-new-company-rate-option-button').on('click', function () {
                                companyRateOption_k += 1;
                                $('#company-rate-company-rate-options').find('tbody')
                                    .append('<tr>' + $('#company-rate-new-company-rate-option-block').html().replace(/__id__/g, 'new' + companyRateOption_k) + '</tr>');
                                // select2 on copied row
                                $('#CompanyRateOptions_new' + companyRateOption_k + '_option_id').select2({
                                    theme: 'krajee',
                                    placeholder: '',
                                    language: 'en',
                                    tags: true
                                });
                                $('#CompanyRateOptions_new' + companyRateOption_k + '_component_id').select2({
                                    theme: 'krajee',
                                    placeholder: '',
                                    language: 'en',
                                    tags: true
                                });
                            });

                            // remove CompanyRateOption button
                            $(document).on('click', '.company-rate-remove-company-rate-option-button', function () {
                                $(this).closest('tbody tr').remove();
                            });

                            // select2 on existing rows
                            $('#company-rate-company-rate-options').find('select.addSelect2:visible').select2({
                                theme: 'krajee',
                                placeholder: '',
                                language: 'en',
                                tags: true
                            });

                        </script>
                        <?php JavaScript::end() ?>

                    </fieldset>

                </div>
            </div>

        </div>
    </div>

    <?php echo Html::submitButton('<span class="fa fa-check"></span> ' . ($model->companyRate->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
