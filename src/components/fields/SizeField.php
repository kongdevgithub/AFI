<?php

namespace app\components\fields;

use app\components\fields\validators\SizeFieldValidator;
use app\components\Helper;
use app\models\Size;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * SizeField
 */
class SizeField extends BaseField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);

        $rule = [['valueDecoded'], SizeFieldValidator::className()];
        if ($productToOption->productTypeToOption) {
            $config = $productToOption->productTypeToOption->getConfigDecoded();
            if (isset($config['max_short_side'])) {
                $rule['maxShortSide'] = $config['max_short_side'];
            }
        }
        $rules[] = $rule;

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
        <?php \app\widgets\JavaScript::begin() ?>
        <script>
            // toggle size fields
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_value', function () {
                var $parent = $(this).closest('.table-cell');
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').hide().find('input').prop('disabled', true);
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').hide().find('input').prop('disabled', true);
                $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_depth').hide().find('input').prop('disabled', true);
                if ($(this).val() === '1') {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                } else if ($(this).val() === '2') {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').show().find('input').prop('disabled', false);
                } else if ($(this).val() === '3') {
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_width').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_height').show().find('input').prop('disabled', false);
                    $parent.find('.field-ProductToOptions_<?= $key ?>_valueDecoded_depth').show().find('input').prop('disabled', false);
                }
            });
            $('#ProductToOptions_<?= $key ?>_valueDecoded_value').change();
        </script>
        <?php \app\widgets\JavaScript::end() ?>
        <?php
        $fields = [];

        $value = $productToOption->getValueDecoded();
        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
            'id' => "ProductToOptions_{$key}_valueDecoded_value",
            'name' => "ProductToOptions[$key][valueDecoded][value]",
            'value' => isset($value['value']) ? $value['value'] : '',
            'prompt' => count($data) == 1 ? null : '',
        ])->label($productToOption->option->name);

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
        return Helper::getSizeHtml($productToOption->getValueDecoded());
    }

    /**
     * @inheritdoc
     */
    public function nameProduct($productToOption)
    {
        $size = $productToOption->getValueDecoded();
        return $size ? Helper::getSizeHtml($size, false) : '';
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