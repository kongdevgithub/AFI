<?php

namespace app\components\fields;

use app\models\Component;
use app\models\ProductToOption;
use Yii;

/**
 * IlluminatedPowerRearField
 */
class IlluminatedPowerRearField extends ComponentField
{

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $quantity = $this->getQuantity($productToOption);
        if ($quantity > 0) {
            $component = $this->getComponent($productToOption);
            if ($component) {
                return $component->name . ' x' . $quantity;
            }
        }
        return '';
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $data = [
            'auto' => Yii::t('app', 'Auto Select'),
            'none' => Yii::t('app', 'No LEDs'),
        ];
        return $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            //'prompt' => '',
        ])->label($productToOption->option->name);
    }

    /**
     * @param ProductToOption $productToOption
     * @return Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $autoComponent = $this->autoFindComponent($productToOption);
            if ($autoComponent && $autoComponent['component_id']) {
                $this->_component = Component::findOne($autoComponent['component_id']);
            }
        }
        return $this->_component;
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuantity($productToOption)
    {
        $autoComponent = $this->autoFindComponent($productToOption);
        return $autoComponent ? $autoComponent['quantity'] * $productToOption->quantity : 0;
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    private function autoFindComponent($productToOption)
    {
        $cacheKey = 'IlluminatedPowerRearField.autoFindComponent.' . $productToOption->id;
        $autoComponent = $productToOption->product->getCache($cacheKey);
        if ($autoComponent !== false) {
            return $autoComponent;
        }
        if ($productToOption->getValueDecoded() != 'auto') {
            $autoComponent = null;
            $productToOption->product->setCache($cacheKey, $autoComponent);
            return $autoComponent;
        }

        // get available components
        $query = Component::find();
        $fieldConfig = $productToOption->option->getFieldConfigDecoded();
        if (isset($fieldConfig['condition'])) {
            $query->andWhere($fieldConfig['condition']);
        }
        if ($productToOption->productTypeToOption) {
            $values = $productToOption->productTypeToOption->getValuesDecoded();
            if ($values) {
                $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
            }
        }
        $ledPowers = [];
        foreach ($query->orderBy(['name' => SORT_ASC])->all() as $_component) {
            $componentConfig = $_component->getConfigDecoded();
            if (isset($componentConfig['led_power']['rear'])) {
                $ledPowers[$_component->id] = $componentConfig['led_power']['rear'];
            }
        }
        asort($ledPowers);
        $maxCapacity = max($ledPowers);

        // get best matched component and quantity
        $size = $productToOption->item_id ? $productToOption->item->getSize() : $productToOption->product->getSize();
        $maxSize = max($size);
        $minSize = min($size);
        $component_id = false;
        $powerCapacity = ceil($maxSize / 550) * ceil($minSize / 85);
        $powerQuantity = 1;
        if ($powerCapacity >= $maxCapacity) {
            $powerQuantity = ceil($powerCapacity / $maxCapacity);
            $powerCapacity = ceil($powerCapacity / $powerQuantity);
        }
        foreach ($ledPowers as $component_id => $ledPower) {
            if ($ledPower >= $powerCapacity) {
                break;
            }
        }

        $autoComponent = [
            'component_id' => $component_id,
            'quantity' => $powerQuantity,
        ];
        $productToOption->product->setCache($cacheKey, $autoComponent);

        return $autoComponent;
    }

}