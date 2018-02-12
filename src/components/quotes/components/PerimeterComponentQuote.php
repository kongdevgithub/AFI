<?php

namespace app\components\quotes\components;

use Yii;

/**
 * PerimeterComponentQuote
 */
class PerimeterComponentQuote extends BaseComponentQuote
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
        return $unitQuantity * $this->getPerimeter($item) / 1000;
    }

}