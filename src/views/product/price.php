<?php

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductPriceForm $model
 * @var ActiveForm $form
 */

$this->title = $model->product->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Price');

$ru = !empty($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="product-price">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Price'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Product',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($model);
            ?>

            <div class="row">
                <div class="col-md-6">
                    <?php
                    echo $form->field($model->product, 'quote_total_price')->textInput(['disabled' => true])->label(Yii::t('app', 'Quote Price'));
                    echo $form->field($model, 'zero_factor')->checkbox();
                    echo $form->field($model, 'factor')->textInput();
                    echo $form->field($model, 'factor_price')->textInput();
                    echo $form->field($model, 'retail_price')->textInput();
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    echo $form->field($model, 'area')->textInput(['disabled' => true]);
                    echo $form->field($model, 'area_price')->textInput();
                    echo $form->field($model, 'perimeter')->textInput(['disabled' => true]);
                    echo $form->field($model, 'perimeter_price')->textInput();
                    echo $form->field($model, 'preserve_unit_prices')->checkbox();
                    echo $form->field($model, 'prevent_rate_prices')->checkbox();
                    ?>
                </div>
            </div>

            <?php
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>
        ZeroFactor = {};
        ZeroFactor.actualValue = false;
        ZeroFactor.lastChecked = "unknown";
        ZeroFactor.igoreClick = false;
        ZeroFactor.click = function (e) {
            if (ZeroFactor.igoreClick) {
                return true;
            }
            var $zeroCheckBox = $("#productpriceform-zero_factor");
            var isChecked = $zeroCheckBox.is(':checked');
            //to avoid double trigger on change and click
            if (isChecked && (ZeroFactor.lastChecked === "checked")) {
                return;
            }
            if (!isChecked && (ZeroFactor.lastChecked === "unchecked")) {
                return;
            }
            ZeroFactor.lastChecked = isChecked ? "checked" : "unchecked";

            var $productPriceFormFactor = $('#productpriceform-factor');
            var currentFactor = $productPriceFormFactor.val();
            //in db field is decimal(11,8) (jobflow4_data/item/quote_factor)
            var updatedFactorValue = "0.0000001";
            if (!isChecked) {
                //unless old value is
                updatedFactorValue = 1;
            }
            if (currentFactor < 0.001) {
                if (ZeroFactor.actualValue) {
                    updatedFactorValue = ZeroFactor.actualValue;
                }
            }
            else {
                ZeroFactor.actualValue = currentFactor;
            }
            $productPriceFormFactor.val(updatedFactorValue);
            updateFactor(e);
        };
        ZeroFactor.fixStatusOnLoad = function () {
            var $productPriceFormFactor = $('#productpriceform-factor');
            var $zeroCheckBox = $("#productpriceform-zero_factor");
            var factorValue = $productPriceFormFactor.val();
            if (factorValue < 0.001) {
                ZeroFactor.igoreClick = false;
                $zeroCheckBox.prop('checked', true);
                ZeroFactor.igoreClick = false;
            }
        };
        ZeroFactor.fixStatusOnLoad();

        // update curve fields
        $(document).on('change', '#productpriceform-factor', updateFactor);
        $(document).on('change', '#productpriceform-factor_price', updateFactorPrice);
        $(document).on('change', '#productpriceform-retail_price', updateRetailPrice);
        $(document).on('change', '#productpriceform-area_price', updateAreaPrice);
        $(document).on('change', '#productpriceform-perimeter_price', updatePerimeterPrice);
        $(document).on('change', '#productpriceform-zero_factor', ZeroFactor.click);
        $(document).on('click', '#productpriceform-zero_factor', ZeroFactor.click);

        function updateFactor(e) {
            var quotePrice = $('#product-quote_total_price').val(),
                area = $('#productpriceform-area').val(),
                perimeter = $('#productpriceform-perimeter').val(),
                factor = $("#productpriceform-factor").val(),
                factorPrice = quotePrice * factor,
                retailPrice = factorPrice * <?= $model->product->job->quote_markup ?>,
                areaPrice = retailPrice / area,
                perimeterPrice = retailPrice / perimeter;
            $('#productpriceform-factor_price').val(round(factorPrice, 2));
            $('#productpriceform-retail_price').val(round(retailPrice, 2));
            $('#productpriceform-area_price').val(round(areaPrice, 2));
            $('#productpriceform-perimeter_price').val(round(perimeterPrice, 2));
        }

        function updateFactorPrice(e) {
            var quotePrice = $('#product-quote_total_price').val(),
                area = $('#productpriceform-area').val(),
                perimeter = $('#productpriceform-perimeter').val(),
                factorPrice = $(this).val(),
                factor = factorPrice / quotePrice,
                retailPrice = factorPrice * <?= $model->product->job->quote_markup ?>,
                areaPrice = retailPrice / area,
                perimeterPrice = retailPrice / perimeter;
            $('#productpriceform-factor').val(round(factor, 8));
            $('#productpriceform-retail_price').val(round(retailPrice, 2));
            $('#productpriceform-area_price').val(round(areaPrice, 2));
            $('#productpriceform-perimeter_price').val(round(perimeterPrice, 2));
        }

        function updateRetailPrice(e) {
            var quotePrice = $('#product-quote_total_price').val(),
                area = $('#productpriceform-area').val(),
                perimeter = $('#productpriceform-perimeter').val(),
                retailPrice = $(this).val(),
                factor = retailPrice / quotePrice / <?= $model->product->job->quote_markup ?>,
                factorPrice = retailPrice / <?= $model->product->job->quote_markup ?>,
                areaPrice = retailPrice / area,
                perimeterPrice = retailPrice / perimeter;
            $('#productpriceform-factor').val(round(factor, 8));
            $('#productpriceform-factor_price').val(round(factorPrice, 2));
            $('#productpriceform-area_price').val(round(areaPrice, 2));
            $('#productpriceform-perimeter_price').val(round(perimeterPrice, 2));
        }

        function updateAreaPrice(e) {
            var quotePrice = $('#product-quote_total_price').val(),
                area = $('#productpriceform-area').val(),
                perimeter = $('#productpriceform-perimeter').val(),
                areaPrice = $(this).val(),
                retailPrice = areaPrice * area,
                factor = retailPrice / quotePrice / <?= $model->product->job->quote_markup ?>,
                factorPrice = retailPrice / <?= $model->product->job->quote_markup ?>,
                perimeterPrice = retailPrice / perimeter;
            $('#productpriceform-factor').val(round(factor, 8));
            $('#productpriceform-factor_price').val(round(factorPrice, 2));
            $('#productpriceform-retail_price').val(round(retailPrice, 2));
            $('#productpriceform-perimeter_price').val(round(perimeterPrice, 2));
        }

        function updatePerimeterPrice(e) {
            var quotePrice = $('#product-quote_total_price').val(),
                area = $('#productpriceform-area').val(),
                perimeter = $('#productpriceform-perimeter').val(),
                perimeterPrice = $(this).val(),
                retailPrice = perimeterPrice * perimeter,
                factor = retailPrice / quotePrice / <?= $model->product->job->quote_markup ?>,
                factorPrice = retailPrice / <?= $model->product->job->quote_markup ?>,
                areaPrice = retailPrice / area;
            $('#productpriceform-factor').val(round(factor, 8));
            $('#productpriceform-factor_price').val(round(factorPrice, 2));
            $('#productpriceform-retail_price').val(round(retailPrice, 2));
            $('#productpriceform-area_price').val(round(areaPrice, 2));
        }

        function round(value, decimals) {
            return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
        }

    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

