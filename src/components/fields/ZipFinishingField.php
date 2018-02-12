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
 * ZipFinishingField
 */
class ZipFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_SEWING = 11838;
    const COMPONENT_ZIP = 12127;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $zip = Component::findOne(static::COMPONENT_ZIP);
            $sewing = Component::findOne(static::COMPONENT_SEWING);
            //$seam->name .= ' + ' . $sewing->name;
            $zip->make_ready_cost += $sewing->make_ready_cost;
            $zip->unit_cost += $sewing->unit_cost;
            $zip->minimum_cost += $sewing->minimum_cost;
            $this->_component = $zip;
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