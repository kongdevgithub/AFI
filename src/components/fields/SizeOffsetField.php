<?php

namespace app\components\fields;

use app\components\fields\validators\SizeFieldValidator;
use app\models\Size;
use app\widgets\JavaScript;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * SizeOffsetField
 */
class SizeOffsetField extends BaseField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        //$rules[] = [['valueDecoded'], SizeFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $query = Size::find();
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded()) {
            $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
        }
        $data = ArrayHelper::map($query->all(), 'id', 'name');
        ?>
        <?php JavaScript::begin() ?>
        <script>
            // toggle size fields
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_value', function () {
                var $parent = $(this).closest('.table-cell');
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').hide().find('input').prop('disabled', true);
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').hide().find('input').prop('disabled', true);
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_depth').hide().find('input').prop('disabled', true);
                if ($(this).val() === 1) {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                } else if ($(this).val() === 2) {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').show().find('input').prop('disabled', false);
                } else if ($(this).val() === 3) {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_depth').show().find('input').prop('disabled', false);
                }
            });
            $('#ProductToOptions_<?= $key ?>_valueDecoded_value').change();
        </script>
        <?php JavaScript::end() ?>
        <?php
        $fields = [];

        $value = $productToOption->getValueDecoded();

        if (count($data) == 1 && $productToOption->productTypeToOption && $productToOption->productTypeToOption->required) {
            $fields[] = Html::hiddenInput("ProductToOptions[$key][valueDecoded][value]", !empty($value['value']) ? $value['value'] : key($data), [
                'id' => "ProductToOptions_{$key}_valueDecoded_value",
            ]);
        } else {
            $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
                'id' => "ProductToOptions_{$key}_valueDecoded_value",
                'name' => "ProductToOptions[$key][valueDecoded][value]",
                'value' => isset($value['value']) ? $value['value'] : '',
                'prompt' => '',
            ])->label($productToOption->option->name);
        }

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_width",
            'name' => "ProductToOptions[$key][valueDecoded][width]",
            'value' => isset($value['width']) ? $value['width'] : '',
        ])->label(Yii::t('app', 'Width'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_height",
            'name' => "ProductToOptions[$key][valueDecoded][height]",
            'value' => isset($value['height']) ? $value['height'] : '',
        ])->label(Yii::t('app', 'Height'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_depth",
            'name' => "ProductToOptions[$key][valueDecoded][depth]",
            'value' => isset($value['depth']) ? $value['depth'] : '',
        ])->label(Yii::t('app', 'Depth'));

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $attributeValue = [];
        $_size = $productToOption->getValueDecoded();
        if (isset($_size['value'])) {
            $size = Size::findOne($_size['value']);
            if ($size) {
                if ($size->width) {
                    $_size['width'] = $size->width;
                    $label[] = $size->width . 'W';
                }
                if ($size->height) {
                    $_size['height'] = $size->height;
                    $label[] = $size->width . 'H';
                }
                if ($size->depth) {
                    $_size['depth'] = $size->depth;
                    $label[] = $size->width . 'D';
                }
            }
            unset($_size['value']);
        }

        $label = [];
        if (!empty($_size['width'])) {
            $label[] = $_size['width'] . 'W';
        }
        if (!empty($_size['height'])) {
            $label[] = $_size['height'] . 'H';
        }
        if (!empty($_size['depth'])) {
            $label[] = $_size['depth'] . 'D';
        }
        $attributeValue[] = implode(' x ', $label);

        //if (!empty($value['width'])) {
        //    $size[] = Yii::t('app', 'Width') . ': ' . $value['width'];
        //}
        //if (!empty($value['height'])) {
        //    $size[] = Yii::t('app', 'Height') . ': ' . $value['height'];
        //}
        //if (!empty($value['depth'])) {
        //    $size[] = Yii::t('app', 'Depth') . ': ' . $value['depth'];
        //}
        if (!empty($_size['width']) && !empty($_size['height'])) {
            //$quantity = $productToOption->product->quantity;
            $attributeValue[] = '<small>' . implode(' | ', [
                    Yii::t('app', 'Area') . ': ' . round(($_size['width'] / 1000) * ($_size['height'] / 1000), 3) . 'm<sup>2</sup>',
                    Yii::t('app', 'Perimeter') . ': ' . round((($_size['width'] / 1000) + ($_size['height'] / 1000)) * 2, 3) . 'm',
                ]) . '</small>';
            //$size[] = '<small>Total: ' . implode(' | ', [
            //        Yii::t('app', 'Area') . ': ' . round(($value['width'] / 1000) * ($value['height'] / 1000) * $quantity, 3) . 'm<sup>2</sup>',
            //        Yii::t('app', 'Perimeter') . ': ' . round((($value['width'] / 1000) + ($value['height'] / 1000)) * 2 * $quantity, 3) . 'm',
            //    ]) . '</small>';
        }
        return implode('<br>', $attributeValue);
    }

    /**
     * @inheritdoc
     */
    public function nameProduct($productToOption)
    {
        $size = [];
        $value = $productToOption->getValueDecoded();
        if (!empty($value['width'])) {
            $size[] = $value['width'];
        }
        if (!empty($value['height'])) {
            $size[] = $value['height'];
        }
        if (!empty($value['depth'])) {
            $size[] = $value['depth'];
        }
        return implode('x', $size);
    }

    /**
     * @inheritdoc
     */
    public function fieldProductType($productTypeToOption, $form)
    {
        $select2Options = [
            'multiple' => true,
            'theme' => 'krajee',
            'placeholder' => '',
            'language' => 'en-US',
            'width' => '100%',
            //'allowClear' => true,
        ];
        $data = ArrayHelper::map(Size::find()->all(), 'id', 'name');
        return $form->field($productTypeToOption, 'valuesDecoded')->widget(Select2::className(), [
            'model' => $productTypeToOption,
            'attribute' => 'values',
            'data' => $data,
            'options' => $select2Options,
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function allowedValues($productTypeToOption)
    {
        $allowedValues = [];
        $values = $productTypeToOption->getValuesDecoded();
        if ($values) {
            foreach ($values as $value) {
                $size = Size::findOne($value);
                if ($size) {
                    $allowedValues[] = $size->name;
                }
            }
        }
        asort($allowedValues);
        return $allowedValues;
    }
}