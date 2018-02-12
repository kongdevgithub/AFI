<?php

namespace app\components\fields;

use app\components\fields\validators\FinishingFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ProductToOption;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * FenceEyeletField
 */
class FenceEyeletField extends ComponentField
{

    /**
     * Eyelet
     */
    const COMPONENT_ID = 11935;

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $data = [
            '500' => '500mm',
            '1000' => '1000mm',
        ];
        return $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'prompt' => '',
        ])->label($productToOption->option->name);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        $size = $productToOption->item->getSize();
        $perimeter = ($size['width'] + $size['height']) * 2;
        return ceil($perimeter / $productToOption->getValueDecoded()) * $productToOption->quantity;
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        return $component ? $component->name . ' (' . $productToOption->getValueDecoded() . 'mm)' : '';
    }

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $this->_component = Component::findOne(static::COMPONENT_ID);
        }
        return $this->_component;
    }

}