<?php

namespace app\components\fields;

use app\components\fields\validators\FinishingFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * SisterClipFinishingField
 */
class SisterClipFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_SISTER_CLIP = 11988;
    const COMPONENT_SEWING = 11838;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $sisterClip = Component::findOne(static::COMPONENT_SISTER_CLIP);
            $sewing = Component::findOne(static::COMPONENT_SEWING);
            //$seam->name .= ' + ' . $sewing->name;
            $sisterClip->make_ready_cost += $sewing->make_ready_cost;
            $sisterClip->unit_cost += $sewing->unit_cost;
            $sisterClip->minimum_cost += $sewing->minimum_cost;
            $sisterClip->quote_class = 'app\components\quotes\components\BaseComponentQuote';
            $this->_component = $sisterClip;
        }
        return $this->_component;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        if (empty($productToOption->valueDecoded['position'])) {
            return 0;
        }

        $quantity = 0;
        $size = $productToOption->item ? $productToOption->item->getSize() : $productToOption->product->getSize();
        $positions = $productToOption->valueDecoded['position'];
        $spacing = 1000;

        if (isset($positions[0])) {
            $quantity += ceil($size['width'] / $spacing) + 1;
        }
        if (isset($positions[1])) {
            $quantity += ceil($size['width'] / $spacing) + 1;
        }
        if (isset($positions[2])) {
            $quantity += ceil($size['height'] / $spacing) + 1;
        }
        if (isset($positions[3])) {
            $quantity += ceil($size['height'] / $spacing) + 1;
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
        $positions = $this->optsPosition();
        $fields = [];
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);
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
            return $component->name . ' (' . implode(', ', $positions) . ') x' . (count($productToOption->valueDecoded['position']) * 2);
        }
        return '';
    }
}