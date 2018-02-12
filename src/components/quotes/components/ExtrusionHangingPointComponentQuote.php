<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionHangingPointComponentQuote
 */
class ExtrusionHangingPointComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Upto 2m use 2, over 2m use 1 per 1m.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // width under 2m
        if ($size['width'] <= 2000) {
            return $unitQuantity * 2;
        }

        return $unitQuantity * floor($size['width'] / 1000);
    }

}