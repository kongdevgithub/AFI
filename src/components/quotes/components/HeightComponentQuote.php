<?php

namespace app\components\quotes\components;

use Yii;

/**
 * HeightComponentQuote
 */
class HeightComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the height.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * $this->getHeight($item) / 1000;
    }

}