<?php

namespace app\components\fields;

use app\models\Component;
use app\models\ProductToOption;
use Yii;
use yii\helpers\Html;

/**
 * PowderCoatField
 */
class PowderCoatField extends ComponentField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $items = [
            'Satin Black',
            'Satin White',
            'Shoji White',
            'Precious Silver pearl',
            'Gunmetal Kinetic Satin',
            'Gold Pearl',
            'Mannex White',
            'Mannex Black',
            'Other',
        ];
        $items = array_combine($items, $items);

        return $form->field($productToOption, 'valueDecoded')->dropDownList($items, [
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'prompt' => '',
        ])->label($productToOption->option->name);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        $value = $productToOption->getValueDecoded();
        return $component && $value ? $component->name . ' (' . $value . ')' : '';
    }

    /**
     * @param ProductToOption $productToOption
     * @return \app\models\Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $this->_component = Component::findOne(12006);
        }
        return $this->_component;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        if (!$productToOption->getValueDecoded()) {
            return 0;
        }
        return $productToOption->quantity;
    }


}