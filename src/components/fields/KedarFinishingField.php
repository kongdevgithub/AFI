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
 * KedarFinishingField
 */
class KedarFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_KEDAR = 11794;
    const COMPONENT_SEWING = 11838;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $kedar = Component::findOne(static::COMPONENT_KEDAR);
            $sewing = Component::findOne(static::COMPONENT_SEWING);
            //$kedar->name .= ' + ' . $sewing->name;
            $kedar->make_ready_cost += $sewing->make_ready_cost;
            $kedar->unit_cost += $sewing->unit_cost;
            $kedar->minimum_cost += $sewing->minimum_cost;
            $this->_component = $kedar;
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

        return implode("\n", $fields);
    }
    
}