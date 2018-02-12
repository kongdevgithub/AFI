<?php

namespace app\components\quotes\components;

use app\models\Item;
use Yii;

/**
 * WidthComponentQuote
 */
class WidthComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the width.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * $this->getWidth($item) / 1000;
    }

}