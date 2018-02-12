<?php

namespace app\components\fields;

use app\models\Component;
use Yii;
use yii\helpers\Html;

/**
 * EyeletFinishingField
 */
class EyeletFinishingField extends FinishingField
{

    /**
     *
     */
    const COMPONENT_EYELET = 11935;
    const COMPONENT_EYELET_MACHINE = 11973;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $eyelet = Component::findOne(static::COMPONENT_EYELET);
            $eyeletMachine = Component::findOne(static::COMPONENT_EYELET_MACHINE);
            //$eyelet->name .= ' + ' . $eyeletMachine->name;
            $eyelet->make_ready_cost += $eyeletMachine->make_ready_cost;
            $eyelet->unit_cost += $eyeletMachine->unit_cost;
            $eyelet->minimum_cost += $eyeletMachine->minimum_cost;
            $eyelet->quote_class = 'app\components\quotes\components\BaseComponentQuote';
            $this->_component = $eyelet;
        }
        return $this->_component;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        $quantity = 0;

        $size = $productToOption->item ? $productToOption->item->getSize() : $productToOption->product->getSize();
        $positions = $productToOption->valueDecoded['position'];
        $spacing = $productToOption->valueDecoded['spacing'];

        if (isset($positions[0])) {
            $quantity += ceil($size['width'] / $spacing);
        }
        if (isset($positions[1])) {
            $quantity += ceil($size['width'] / $spacing);
        }
        if (isset($positions[2])) {
            $quantity += ceil($size['height'] / $spacing);
        }
        if (isset($positions[3])) {
            $quantity += ceil($size['height'] / $spacing);
        }

        return $quantity * $productToOption->quantity;
    }

    /**
     * @inheritdoc
     */
    public function fieldProductType($productTypeToOption, $form)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $fields = [];

        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($this->optsPosition(), [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);

        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($this->optsSpacing(), [
            'id' => "ProductToOptions_{$key}_valueDecoded_spacing",
            'name' => "ProductToOptions[$key][valueDecoded][spacing]",
            //'prompt' => '',
        ])->label(Yii::t('app', 'Spacing'));

        return implode("\n", $fields);
    }

    /**
     * @return array
     */
    public function optsSpacing()
    {
        return [
            '500' => Yii::t('app', '500mm'),
            '200' => Yii::t('app', '200mm'),
            '1000' => Yii::t('app', '1000mm'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return '';
        }
        $positions = [];
        if (!empty($productToOption->valueDecoded['position'])) {
            if (count($productToOption->valueDecoded['position']) == 4) {
                $positions[] = Yii::t('app', 'All Sides');
            } else {
                foreach ($productToOption->valueDecoded['position'] as $position) {
                    $positions[] = $this->optsPosition()[$position];
                }
            }
            $spacing = $productToOption->valueDecoded['spacing'];
            return $component->name . ' (' . implode(', ', $positions) . ' - ' . $spacing . 'mm)';
        }
        return '';
    }
}