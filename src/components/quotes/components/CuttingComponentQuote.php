<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Option;
use Yii;

/**
 * CuttingComponentQuote
 */
class CuttingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the perimeter.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        if ($this->hasSubstrateBackComponent($item)) {
            $unitQuantity *= 2;
        }
        return $unitQuantity * $this->getPerimeter($item) / 1000;
    }

    /**
     * @param $item
     * @return bool
     */
    private function hasSubstrateBackComponent($item)
    {
        $substrateOption = $this->getProductToOption($item, Option::OPTION_SUBSTRATE_BACK);
        if (!$substrateOption) {
            return false;
        }
        $component_id = $substrateOption->getValueDecoded();
        if (!$component_id) {
            return false;
        }
        $substrateComponent = Component::findOne($component_id);
        if (!$substrateComponent) {
            return false;
        }
        return true;
    }

}