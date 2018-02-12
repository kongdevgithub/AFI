<?php

namespace app\components\fields;

use Yii;

/**
 * TextareaField
 */
class TextareaField extends TextField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $placeholder = isset($productToOption->option->field_config['placeholder']) ? $productToOption->option->field_config['placeholder'] : '';
        return $form->field($productToOption, 'valueDecoded')->textarea([
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'placeholder' => $placeholder,
        ])->label($productToOption->option->name);
    }
}