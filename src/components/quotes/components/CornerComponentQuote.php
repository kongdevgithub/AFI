<?php

namespace app\components\quotes\components;

use Yii;

/**
 * CornerComponentQuote
 */
class CornerComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the number of corner options.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        if (empty($options['position'])) {
            return 0;
        }
        return $unitQuantity * count($options['position']);
    }

}