<?php

namespace app\components\quotes\components;

use app\models\Option;
use Yii;

/**
 * LedRearComponentQuote
 */
class LedRearComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Rear LED used every 550mm on longest side and 85mm on shortest side.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $productToOption = $item->getProductToOption(Option::OPTION_ILLUMINATED_REAR);
        if ($productToOption->getValueDecoded() != 'auto') {
            return 0;
        }

        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        $maxSize = max($size);
        $minSize = min($size);
        return $unitQuantity * ceil($maxSize / 550) * ceil($minSize / 85);
    }

}