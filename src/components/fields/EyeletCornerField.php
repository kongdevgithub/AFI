<?php

namespace app\components\fields;

use app\components\fields\validators\CornerFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * EyeletCornerField
 */
class EyeletCornerField extends CornerField
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
        if (empty($productToOption->valueDecoded['position'])) {
            return 0;
        }
        return count($productToOption->valueDecoded['position']) * $productToOption->quantity;
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

        //$value = $productToOption->value;
        //$productToOption->value = isset($value['position']) ? $value['position'] : [];
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);
        //$productToOption->value = $value;

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
                $positions[] = Yii::t('app', 'All Corners');
            } else {
                foreach ($productToOption->valueDecoded['position'] as $position) {
                    $positions[] = $this->optsPosition()[$position];
                }
            }
            return Yii::t('app', 'Eyelet Corner') . ' (' . implode(', ', $positions) . ')';
        }
        return '';
    }
}