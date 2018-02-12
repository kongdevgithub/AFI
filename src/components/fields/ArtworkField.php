<?php

namespace app\components\fields;

use app\components\fields\validators\ArtworkFieldValidator;
use app\models\Component;
use app\models\ProductToOption;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * ArtworkField
 */
class ArtworkField extends ComponentField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], ArtworkFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        if ($productToOption->quantity > 0) {
            $component = $this->getComponent($productToOption);
            if ($component) {
                return $component->name . ' x' . ($productToOption->quantity * 1);
            }
        }
        return '';
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
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
        $data = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'label');

        $fields = [];
        $value = $productToOption->getValueDecoded();

        if (count($data) == 1) {
            $fields[] = Html::activeHiddenInput($productToOption, 'valueDecoded', [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => key($data),
            ]);
        } else {
            $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => isset($value['component']) ? $value['component'] : '',
                'prompt' => '',
            ])->label(Yii::t('app', 'Component'));
        }

        $fields[] = $form->field($productToOption, 'quantity')->textInput([
            'id' => "ProductToOptions_{$key}_quantity",
            'name' => "ProductToOptions[$key][quantity]",
        ])->label($productToOption->option->name);

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        if (!$productToOption->quantity || !$productToOption->product->quantity || !$productToOption->item->quantity) {
            return 0;
        }
        return $productToOption->quantity / $productToOption->product->quantity / $productToOption->item->quantity;
    }

    /**
     * @param ProductToOption $productToOption
     * @return Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $value = $productToOption->getValueDecoded();
            if (!empty($value['component'])) {
                $this->_component = Component::findOne($value['component']);
            }
        }
        return $this->_component;
    }

}