<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionCuttingComponentQuote
 */
class ExtrusionCuttingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', '1 cut per side.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        return $unitQuantity * 4;
    }

}