<?php

namespace app\components\fields;

use app\components\fields\validators\FinishingFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * RodFabricationField
 */
class RodFabricationField extends ComponentField
{

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $fields = [];

        $query = Component::find()->notDeleted();
        $fieldConfig = $productToOption->option->getFieldConfigDecoded();
        $default = isset($fieldConfig['default']) ? $fieldConfig['default'] : null;

        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded()) {
            $values = $productToOption->productTypeToOption->getValuesDecoded();
            if ($values) {
                $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
            }
        }
        $data = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'label');
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->required && count($data) == 1) {
            $default = key($data);
        }

        $fields[] = $form->field($productToOption, 'valueDecoded')->widget(Select2::className(), [
            'data' => isset($data) ? $data : [],
            'options' => [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => !empty($productToOption->valueDecoded['component']) ? $productToOption->valueDecoded['component'] : $default,
                'theme' => 'krajee',
                'placeholder' => '',
                'language' => 'en-US',
                'width' => '100%',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label($productToOption->option->name);


        $positions = $this->optsPosition();
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);

        //$types = $this->optsType();
        //$fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($types, [
        //    'id' => "ProductToOptions_{$key}_valueDecoded_type",
        //    'name' => "ProductToOptions[$key][valueDecoded][type]",
        //    'value' => isset($productToOption->valueDecoded['type']) ? $productToOption->valueDecoded['type'] : '',
        //])->label($productToOption->option->name . ' ' . Yii::t('app', 'Type'));

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
            foreach ($productToOption->valueDecoded['position'] as $position) {
                $positions[] = $this->optsPosition()[$position];
            }
            return $component->name . ' (' . implode(', ', $positions) . ')';
        }
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            if (!empty($productToOption->valueDecoded['component'])) {
                $this->_component = Component::findOne($productToOption->valueDecoded['component']);
            }
        }

        // legacy
        if (!$this->_component) {
            if (isset($productToOption->valueDecoded['type'])) {
                $component_id = 12013; // aluminium
                if ($productToOption->valueDecoded['type'] == 'dowel') {
                    $component_id = 14456; // dowel
                }
                $this->_component = Component::findOne($component_id);
            }
        }

        return $this->_component;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }

        $quantity = 0;
        $size = $productToOption->item ? $productToOption->item->getSize() : $productToOption->product->getSize();
        $positions = $productToOption->valueDecoded['position'];

        if (isset($positions[0])) {
            $quantity += ceil($size['width'] / 1000);
        }
        if (isset($positions[1])) {
            $quantity += ceil($size['width'] / 1000);
        }

        return $quantity * $productToOption->quantity;
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