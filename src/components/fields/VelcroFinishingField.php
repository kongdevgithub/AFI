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
 * VelcroFinishingField
 */
class VelcroFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_VELCRO_HOOK = 12014;
    const COMPONENT_VELCRO_LOOP = 11964;
    const COMPONENT_SEWING = 11838;
    const COMPONENT_ADHESIVE = 14455;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {

            // which velcro to use
            $velcro_component_id = static::COMPONENT_VELCRO_HOOK;
            if (isset($productToOption->valueDecoded['side']) && $productToOption->valueDecoded['side'] == 'loop') {
                $velcro_component_id = static::COMPONENT_VELCRO_LOOP;
            }
            $velcro = Component::findOne($velcro_component_id);

            // how to apply it
            $apply_component_id = static::COMPONENT_SEWING;
            if (isset($productToOption->valueDecoded['apply']) && $productToOption->valueDecoded['apply'] == 'adhesive') {
                $apply_component_id = static::COMPONENT_ADHESIVE;
            }
            $apply = Component::findOne($apply_component_id);

            $velcro->name .= ' + ' . $apply->name;
            $velcro->make_ready_cost += $apply->make_ready_cost;
            $velcro->unit_cost += $apply->unit_cost;
            $velcro->minimum_cost += $apply->minimum_cost;
            $this->_component = $velcro;
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
        $fields = [];

        $positions = $this->optsPosition();
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);

        $sides = $this->optsSide();
        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($sides, [
            'id' => "ProductToOptions_{$key}_valueDecoded_side",
            'name' => "ProductToOptions[$key][valueDecoded][side]",
            'value' => isset($productToOption->valueDecoded['side']) ? $productToOption->valueDecoded['side'] : '',
        ])->label($productToOption->option->name . ' ' . Yii::t('app', 'Side'));

        $applies = $this->optsApply();
        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($applies, [
            'id' => "ProductToOptions_{$key}_valueDecoded_apply",
            'name' => "ProductToOptions[$key][valueDecoded][apply]",
            'value' => isset($productToOption->valueDecoded['apply']) ? $productToOption->valueDecoded['apply'] : '',
        ])->label($productToOption->option->name . ' ' . Yii::t('app', 'Apply'));

        return implode("\n", $fields);
    }

    /**
     * @return array
     */
    public function optsSide()
    {
        return [
            'hook' => Yii::t('app', 'Hook'),
            'loop' => Yii::t('app', 'Loop'),
        ];
    }

    /**
     * @return array
     */
    public function optsApply()
    {
        return [
            'sewing' => Yii::t('app', 'Sewing'),
            'adhesive' => Yii::t('app', 'Adhesive'),
        ];
    }

}