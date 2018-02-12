<?php

namespace app\components\fields;

use app\models\Component;
use Yii;

/**
 * HeaderTapeFinishingField
 */
class HeaderTapeFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_HEADER_TAPE = 12010;
    const COMPONENT_SEWING = 11838;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $headerTape = Component::findOne(static::COMPONENT_HEADER_TAPE);
            $sewing = Component::findOne(static::COMPONENT_SEWING);
            //$pocketHeader->name .= ' + ' . $sewing->name;
            $headerTape->make_ready_cost += $sewing->make_ready_cost;
            $headerTape->unit_cost += $sewing->unit_cost;
            $headerTape->minimum_cost += $sewing->minimum_cost;
            $this->_component = $headerTape;
        }
        return $this->_component;
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
        $positions = $this->optsPosition();
        $fields = [];

        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);

        $fields[] = $form->field($productToOption, 'valueDecoded', [
            'addon' => [
                'append' => ['content' => 'mm'],
            ]
        ])->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_size",
            'name' => "ProductToOptions[$key][valueDecoded][size]",
            'value' => isset($productToOption->valueDecoded['size']) ? $productToOption->valueDecoded['size'] : '',
            'prompt' => '',
        ])->label(Yii::t('app', 'Size'));

        return implode("\n", $fields);
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
            $size = $productToOption->valueDecoded['size'];
            return $component->name . ' (' . implode(', ', $positions) . ' - ' . $size . 'mm)';
        }
        return '';
    }
}