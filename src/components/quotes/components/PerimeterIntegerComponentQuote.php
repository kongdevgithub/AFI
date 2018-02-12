<?php

namespace app\components\quotes\components;

use Yii;

/**
 * PerimeterIntegerComponentQuote
 */
class PerimeterIntegerComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the perimeter rounded to an integer.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * ceil($this->getPerimeter($item) / 1000);
    }

}