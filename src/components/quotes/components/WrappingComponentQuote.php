<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Product;
use Yii;

/**
 * WrappingComponentQuote
 */
class WrappingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the longest side in 1 unit intervals, capped at 3.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);
        if (!$size) {
            return 0;
        }
        $maxSize = ceil(max($size) / 1000);
        if ($maxSize > 3) {
            $maxSize = 3;
        }
        return $unitQuantity * $maxSize;
    }

}