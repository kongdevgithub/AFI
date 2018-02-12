<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\Option;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
use dektrium\user\models\User;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use zhuravljov\widgets\DatePicker;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemSplitForm $model
 * @var ActiveForm $form
 */

$this->title = $model->item->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->item->product->job->vid . ': ' . $model->item->product->job->name, 'url' => ['/job/view', 'id' => $model->item->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->item->product->id . ': ' . $model->item->product->name, 'url' => ['/product/view', 'id' => $model->item->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->item->id . ': ' . $model->item->name, 'url' => ['view', 'id' => $model->item->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Split');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="item-quantity">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Split'); ?></h3>
        </div>
        <div class="box-body">

            <?php
            $form = ActiveForm::begin([
                'id' => 'Item',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $model->errorSummary($form);
            ?>
            <div class="row">
                <div class="col-md-3">
                    <?php
                    echo $form->field($model, 'item_count')->textInput();
                    echo $form->field($model, 'unit_count')->textInput(['disabled' => true]);
                    echo $form->field($model, 'assigned_units')->textInput(['disabled' => true]);
                    ?>
                </div>
                <div class="col-md-9">
                    <?php
                    echo '<div id="quantities-template" style="display:none;">';
                    echo $form->field($model, 'quantities[]')->textInput()->label(false);
                    echo '</div>';
                    echo '<div id="quantities"></div>';
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
        $('#itemsplitform-item_count').change(function () {
            var $quantities = $('#quantities'),
                itemCount = $(this).val(),
                unitCount = $('#itemsplitform-unit_count').val(),
                template = $('#quantities-template').html(),
                $template = $('<div>').append($(template)),
                $input = $template.find('input'),
                id = $input.attr('id'),
                suggestedQuantity = parseInt(unitCount / itemCount);
            $quantities.find('.field-itemsplitform-quantities').remove();
            for (i = 0; i < itemCount; i++) {
                $input.attr('value', suggestedQuantity);
                $input.attr('id', id + '_' + i);
                $quantities.append($template.html());
            }
            updateAssignedUnits();
        }).change();
        $(document).on('change', '.field-itemsplitform-quantities input', function () {
            updateAssignedUnits();
        });
        function updateAssignedUnits() {
            var total = 0;
            $('#quantities').find('input').each(function () {
                total += parseInt($(this).val());
            });
            $('#itemsplitform-assigned_units').val(total);
        }
    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

