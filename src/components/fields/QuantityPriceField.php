<?php

namespace app\components\fields;

use app\components\fields\validators\QuantityPriceFieldValidator;

/**
 * QuantityPriceField
 */
class QuantityPriceField extends QuantityField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], QuantityPriceFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        if ($productToOption->quote_quantity > 0) {
            $component = $this->getComponent($productToOption);
            if ($component) {
                return $component->name; // . ' x' . ($productToOption->quote_quantity * 1);
            }
        }
        return '';
    }

}