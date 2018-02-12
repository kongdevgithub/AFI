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
 * RodFinishingField
 */
class RodFinishingField extends FinishingField
{
    /**
     *
     */
    const COMPONENT_SEWING = 11838;

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $this->_component = Component::findOne(static::COMPONENT_SEWING);
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

    /**
     * @return array
     */
    public function optsPosition()
    {
        return [
            Yii::t('app', 'Top'),
            Yii::t('app', 'Bottom'),
        ];
    }
}