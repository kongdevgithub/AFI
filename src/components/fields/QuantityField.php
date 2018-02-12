<?php

namespace app\components\fields;

use app\components\fields\validators\QuantityFieldValidator;
use app\models\Component;
use app\models\ProductToOption;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * QuantityField
 */
class QuantityField extends ComponentField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], QuantityFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        if ($productToOption->quote_quantity > 0) {
            $component = $this->getComponent($productToOption);
            if ($component) {
                $quantity = ' x' . ($productToOption->quote_quantity / ($productToOption->item->quantity * $productToOption->product->quantity) * 1) . $component->unit_of_measure;
                return $component->name . $quantity;
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
        $ajax = null;

        $fieldConfig = $productToOption->option->getFieldConfigDecoded();
        $value = $productToOption->getValueDecoded();


        if (isset($fieldConfig['condition']) || ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded())) {
            if (isset($fieldConfig['condition'])) {
                $query->andWhere($fieldConfig['condition']);
            }
            if ($productToOption->productTypeToOption) {
                $values = $productToOption->productTypeToOption->getValuesDecoded();
                if ($values) {
                    $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
                }
            }
        } else {
            if (isset($value['component'])) {
                $query->andWhere(['id' => $value['component']]);
                $ajax = [
                    'url' => Url::to(['component/json-list']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ];
            } else {
                $query->andWhere('1=0');
            }
        }
        $data = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'label');

        $fields = [];

        //$fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
        //    'id' => "ProductToOptions_{$key}_valueDecoded_component",
        //    'name' => "ProductToOptions[$key][valueDecoded][component]",
        //    'value' => isset($value['component']) ? $value['component'] : '',
        //    'prompt' => '',
        //    'onchange' => "if($(this).val()){ $('.field-ProductToOptions_{$key}_quantity').show(); }else{ $('.field-ProductToOptions_{$key}_quantity').hide(); }",
        //])->label($productToOption->option->name . ' ' . Yii::t('app', 'Component'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->widget(Select2::className(), [
            //'model' => $productToOption,
            //'attribute' => 'valueDecoded',
            'data' => isset($data) ? $data : [],
            'options' => [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => isset($value['component']) ? $value['component'] : '',
                'theme' => 'krajee',
                'placeholder' => '',
                'language' => 'en-US',
                'width' => '100%',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => $ajax ? 1 : 0,
                'ajax' => $ajax,
            ],
            'pluginEvents' => [
                'select2:select' => "function(e) { if(e.params.data.id) { $('.field-ProductToOptions_{$key}_quantity').show(); }else{ $('.field-ProductToOptions_{$key}_quantity').hide(); } }",
            ],
        ])->label($productToOption->option->name);

        $fields[] = $form->field($productToOption, 'quantity', [
            'options' => [
                'class' => 'form-group',
                'style' => $productToOption->valueDecoded && !empty($productToOption->valueDecoded['component']) ? '' : 'display:none',
            ],
        ])->textInput([
            'id' => "ProductToOptions_{$key}_quantity",
            'name' => "ProductToOptions[$key][quantity]",
        ])->label($productToOption->option->name . ' ' . Yii::t('app', 'Qty/Length'));

        return implode("\n", $fields);
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