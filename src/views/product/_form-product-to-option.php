<?php

use app\components\fields\BaseField;
use app\components\fields\ComponentField;
use app\components\fields\QuantityField;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Option;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\ProductToOption $productToOption
 * @var kartik\form\ActiveForm $form
 * @var string $key
 * @var string $itemKey
 * @var boolean $allowOptionChange allows changing of the option_id
 * @var boolean $allowOptionRemove allows removing of the option from the form
 */

?>

<div class="table-cell">
    <?php
    $output = false;

    $fields = [];

    if ($allowOptionChange) {
        $fields[] = $form->field($productToOption, 'option_id', [
            'addon' => [
                'append' => [
                    'content' => Html::a('<i class="fa fa-minus"></i>', 'javascript:void(0);', [
                        'title' => Yii::t('app', 'Remove'),
                        'class' => 'product-remove-productToOption-button',
                    ]),
                ],
            ],
        ])->dropDownList(ArrayHelper::map(Option::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'), [
            'id' => "ProductToOptions_{$key}_option_id",
            'name' => "ProductToOptions[$key][option_id]",
            'class' => 'form-control ProductToOption_option_id',
            'data-key' => $key,
            'data-item-key' => $itemKey,
            'prompt' => '',
        ]);
        $output = true;
    } else {
        $fields[] = Html::activeHiddenInput($productToOption, 'option_id', [
            'id' => "ProductToOptions_{$key}_option_id",
            'name' => "ProductToOptions[$key][option_id]",
        ]);
    }

    $fields[] = Html::activeHiddenInput($productToOption, 'product_type_to_option_id', [
        'id' => "ProductToOptions_{$key}_product_type_to_option_id",
        'name' => "ProductToOptions[$key][product_type_to_option_id]",
    ]);

    $fields[] = Html::activeHiddenInput($productToOption, 'item_id', [
        'id' => "ProductToOptions_{$key}_item_id",
        'name' => "ProductToOptions[$key][item_id]",
        'value' => $itemKey,
    ]);

    $fields[] = Html::activeHiddenInput($productToOption, 'quote_class', [
        'id' => "ProductToOptions_{$key}_quote_class",
        'name' => "ProductToOptions[$key][quote_class]",
    ]);

    $fields[] = Html::activeHiddenInput($productToOption, 'quantity', [
        'id' => "ProductToOptions_{$key}_quantity",
        'name' => "ProductToOptions[$key][quantity]",
    ]);

    $fields[] = Html::activeHiddenInput($productToOption, 'quote_quantity_factor', [
        'id' => "ProductToOptions_{$key}_quote_quantity_factor",
        'name' => "ProductToOptions[$key][quote_quantity_factor]",
    ]);

    $fieldClassOutput = false;
    if ($productToOption->option) {
        /** @var BaseField $field */
        $field = new $productToOption->option->field_class;
        $fieldClassOutput = $field->fieldProduct($productToOption, $form, $key);
        if ($fieldClassOutput) {
            if ($allowOptionRemove || !$productToOption->product_type_to_option_id) {
                ob_start();
                ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= $productToOption->option->name; ?></h3>
                        <div class="box-tools pull-right">
                            <?= Html::a('<i class="fa fa-minus"></i>', 'javascript:void(0);', [
                                'title' => Yii::t('app', 'Remove'),
                                'class' => 'product-remove-productToOption-button btn btn-box-tool',
                            ]) ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php
                        echo $fieldClassOutput;
                        if ($field instanceof ComponentField) {
                            echo $form->field($productToOption, 'quote_class')->dropDownList(BaseComponentQuote::opts(), [
                                'id' => "ProductToOptions_{$key}_quote_class",
                                'name' => "ProductToOptions[$key][quote_class]",
                                'prompt' => Yii::t('app', 'Inherit'),
                            ]);
                            if (!($field instanceof QuantityField)) {
                                echo $form->field($productToOption, 'quantity')->textInput([
                                    'id' => "ProductToOptions_{$key}_quantity",
                                    'name' => "ProductToOptions[$key][quantity]",
                                ]);
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
                $fieldClassOutput = ob_get_clean();
            }
            $output = true;
        }
    }

    echo implode($fields);
    if ($output) {
        echo $fieldClassOutput;
        //echo '<hr>' . $content;
    }
    ?>
</div>