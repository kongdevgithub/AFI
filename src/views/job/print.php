<?php

use app\components\PrintSpool;
use app\models\Item;
use app\models\ItemType;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobPrintForm $model
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

$itemTypes = [];
foreach ($model->job->products as $product) {
    foreach ($product->items as $item) {
        $itemTypes[$item->item_type_id][$item->id] = $item;
    }
}

?>
<div class="job-print">

    <?php
    $form = ActiveForm::begin([
        'id' => 'JobPrint',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $form->errorSummary($model);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool());
    echo $form->field($model, 'print')->checkboxList($model->optsPrint());
    if ($itemTypes) {
        echo $form->field($model, 'item_types')->checkboxList(ArrayHelper::map(ItemType::find()->andWhere(['in', 'id', array_keys($itemTypes)])->all(), 'id', 'name'));
    }
    ?>

    <?php
    foreach ($itemTypes as $item_type_id => $items) {
        /** @var Item[] $items */
        $itemType = ItemType::findOne($item_type_id);
        ?>
        <div class="item-type-<?= $itemType->id ?>" style="display: none;">
            <h4><?= $itemType->name ?></h4>
            <?php
            foreach ($items as $item) {
                $quantity = $item->quantity * $item->product->quantity;
                if (!$quantity) continue;
                echo $form->field($model, "items[$item->id]")->textInput(['value' => $quantity])->label('i' . $item->id)->hint(implode(' | ', [
                        $item->name,
                        $item->getSizeHtml(),
                        $item->product->name,
                    ]) . '<br>x1=' . $quantity . ', x2=' . ($quantity * 2) . ', x3=' . ($quantity * 3) . ', x4=' . ($quantity * 4) . ', x5=' . ($quantity * 5));
            }
            ?>
        </div>
        <?php
    }
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Print'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>
        var $itemTypeCheckboxes = $('#jobprintform-item_types').find(':input');
        $('#jobprintform-print').find(':input').change(function () {
            $itemTypeCheckboxes.change();
        });
        $itemTypeCheckboxes.change(function () {
            var val = $(this).val(),
                checked = $(this).is(':checked'),
                show = $('#jobprintform-print').find('input[type=checkbox][value=item_label]').is(':checked');
            if (show && checked) {
                $('.item-type-' + val).show();
            } else {
                $('.item-type-' + val).hide();
            }
        });
    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

