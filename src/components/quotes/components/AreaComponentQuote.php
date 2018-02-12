<?php

namespace app\components\quotes\components;

use Yii;

/**
 * AreaComponentQuote
 */
class AreaComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Area in m^2.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * $this->getArea($item) / 1000 / 1000;
    }

}