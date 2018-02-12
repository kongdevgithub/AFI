<?php

namespace app\components\fields;

use Yii;

/**
 * TextField
 */
class TextField extends BaseField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $placeholder = isset($productToOption->option->field_config['placeholder']) ? $productToOption->option->field_config['placeholder'] : '';
        return $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'placeholder' => $placeholder,
        ])->label($productToOption->option->name);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $value = parent::attributeValueProduct($productToOption);
        $addon = isset($productToOption->option->field_config['addon']) ? $productToOption->option->field_config['addon'] : '';
        if (isset($addon['append']['content'])) {
            $value .= ' ' . $addon['append']['content'];
        }
        return $value;
    }
}