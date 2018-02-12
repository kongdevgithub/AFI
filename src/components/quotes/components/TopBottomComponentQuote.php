<?php

namespace app\components\quotes\components;

use app\models\Item;
use Yii;

/**
 * TopBottomComponentQuote
 */
class TopBottomComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the width of the top and bottom.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * ($this->getWidth($item) / 1000) * 2;
    }

}