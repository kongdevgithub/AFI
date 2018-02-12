<?php

use kartik\file\FileInput;
use kartik\form\ActiveForm;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\CompanyRateImportForm $model
 */

$this->title = $model->company->name;
?>
<div class="company-rate-import">

    <?= $this->render('_menu', ['model' => $model->company]); ?>

    <div class="row">
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'CSV Guide') ?></h3>
                    <div class="box-tools pull-right text-right">
                        <?php
                        echo Html::a('<span class="fa fa-download"></span> ' . Yii::t('app', 'download export'), ['rate-export', 'id' => $model->company->id], [
                            'class' => 'btn btn-box-tool',
                        ]);
                        ?>
                    </div>
                </div>
                <div class="box-body">
                    <?php
                    $items = [];
                    $doc = [
                        'product_type' => 'The breadcrumb to the parent Product Type, eg: <code>Flatbed Print > Flatbed</code> or <code>Reframe > Reframe Skin</code>.',
                        'item_type' => 'The name of the Item Type, eg: <code>Print</code> or <code>Fabrication</code>.',
                        'option' => 'The name of the Option that will have a component selected, eg: <code>Substrate</code> or <code>Flatbed Substrate</code>.',
                        'component' => 'The code of the Component that will be selected in the option, eg: <code>MATTEX</code> or <code>FB-IB-3.3MM</code>.',
                        'size' => 'The size of the Product, eg: <code>1200x1800</code> or blank for M2 rate.',
                        'price' => 'The price of the Product (or the M2 rate if no size given), eg: <code>50.00</code>',
                        'options' => 'Additional required options for the rate to apply.  The format is <code>Option1Name=Component1Code,Option2Name=Component2Code</code>, eg: <code>Printer=PRINT,Printer (back)=PRINT</code>.',
                    ];
                    foreach ($doc as $k => $v) {
                        $items[] = Html::tag('code', $k) . ' - ' . $v;
                    }
                    echo Html::ul($items, ['encode' => false]);
                    ?>
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Upload CSV') ?></h3>
                </div>
                <div class="box-body">

                    <?php $form = ActiveForm::begin([
                        'id' => 'Address',
                        'type' => 'horizontal',
                        'enableClientValidation' => false,
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

                    <?= $form->errorSummary($model); ?>

                    <?= $form->field($model, 'upload')->widget(FileInput::className(), [
                        'options' => ['accept' => '*.csv'],
                        'pluginOptions' => [
                            'showPreview' => true,
                            'showCaption' => false,
                            'showRemove' => true,
                            'showUpload' => false,
                        ],
                    ])->label(false); ?>

                    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Import'), [
                        'id' => 'save-' . $model->formName(),
                        'class' => 'btn btn-success',
                    ]); ?>

                    <?php ActiveForm::end(); ?>


                </div>
            </div>

        </div>
    </div>


</div>
