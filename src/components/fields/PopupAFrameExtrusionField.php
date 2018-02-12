<?php

namespace app\components\fields;

use app\models\Component;
use app\models\ProductToOption;
use Yii;

/**
 * PopupAFrameExtrusionField
 */
class PopupAFrameExtrusionField extends ComponentField
{

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        return $component ? $component->name : '';
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $data = ['auto' => Yii::t('app', 'Auto Select')];
        return $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            //'prompt' => '',
        ])->label($productToOption->option->name);
    }

    /**
     * @param ProductToOption $productToOption
     * @return Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            $autoComponent = $this->autoFindComponent($productToOption);
            if ($autoComponent) {
                $this->_component = Component::findOne($autoComponent);
            }
        }
        return $this->_component;
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuantity($productToOption)
    {
        $autoComponent = $this->autoFindComponent($productToOption);
        return $autoComponent ? $autoComponent['quantity'] * $productToOption->quantity : 0;
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    private function autoFindComponent($productToOption)
    {
        $autoComponent = $productToOption->product->getCache('PopupAFrameExtrusionField.autoFindComponent.' . $productToOption->id);
        if ($autoComponent) {
            return $autoComponent;
        }

        // get available components
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
        $popupAFrameExtrusions = [];
        foreach ($query->orderBy(['name' => SORT_ASC])->all() as $_component) {
            $componentConfig = $_component->getConfigDecoded();
            if (isset($componentConfig['popup_a_frame_size'])) {
                $popupAFrameExtrusions[$_component->id] = $componentConfig['popup_a_frame_size'];
            }
        }

        // get best matched component
        $size = $productToOption->item_id ? $productToOption->item->getSize() : $productToOption->product->getSize();
        $autoComponent = array_search($size['width'] . 'x' . $size['height'], $popupAFrameExtrusions);
        $productToOption->product->setCache('PopupAFrameExtrusionField.autoFindComponent.' . $productToOption->id, $autoComponent);
        return $autoComponent;
    }

}