<?php

namespace app\components\quotes\components;

use app\models\Option;
use Yii;

/**
 * FenceEyeletMachineComponentQuote
 */
class FenceEyeletMachineComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the perimeter divided by the spacing, rounded to an integer.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {

        $productToOption = $this->getProductToOption($item, Option::OPTION_FENCE_EYELET);
        $eyeletQuantity = ceil($this->getPerimeter($item) / $productToOption->getValueDecoded());
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * $eyeletQuantity;
    }

}