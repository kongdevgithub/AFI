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
 * SeamFinishingField
 */
class SeamFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_SEAM = 11799;
    const COMPONENT_SEWING = 11838;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $seam = Component::findOne(static::COMPONENT_SEAM);
            $sewing = Component::findOne(static::COMPONENT_SEWING);
            //$seam->name .= ' + ' . $sewing->name;
            $seam->make_ready_cost += $sewing->make_ready_cost;
            $seam->unit_cost += $sewing->unit_cost;
            $seam->minimum_cost += $sewing->minimum_cost;
            $this->_component = $seam;
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