<?php

namespace app\components\fields;

use kartik\select2\Select2;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * SelectField
 */
class SelectField extends BaseField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $items = [];
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->values) {
            $items = $productToOption->productTypeToOption->getValuesDecoded();
            $items = array_combine($items, $items);
        } else {
            $fieldConfig = Json::decode($productToOption->option->field_config);
            if (!empty($fieldConfig['values'])) {
                $items = array_combine($fieldConfig['values'], $fieldConfig['values']);
            }
        }
        $fields = [];

        if (count($items) == 1 && $productToOption->productTypeToOption && $productToOption->productTypeToOption->required) {
            $fields[] = Html::hiddenInput("ProductToOptions[$key][valueDecoded]", !empty($value['value']) ? $value['value'] : key($items), [
                'id' => "ProductToOptions_{$key}_valueDecoded",
            ]);
        } else {
            $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($items, [
                'id' => "ProductToOptions_{$key}_valueDecoded",
                'name' => "ProductToOptions[$key][valueDecoded]",
                'prompt' => '',
            ])->label($productToOption->option->name);
        }

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        if (!$productToOption->valueDecoded) {
            return '';
        }
        return $productToOption->option->name . ': ' . $productToOption->valueDecoded;
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
        $fieldConfig = Json::decode($productTypeToOption->option->field_config);
        $items = [];
        if (!empty($fieldConfig['values'])) {
            $items = array_combine($fieldConfig['values'], $fieldConfig['values']);
        }
        $fields = [];
        $fields[] = $form->field($productTypeToOption, 'valuesDecoded')->widget(Select2::className(), [
            'model' => $productTypeToOption,
            'attribute' => 'valuesDecoded',
            'data' => $items,
            'options' => $select2Options,
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        return implode(' ', $fields);
    }

}