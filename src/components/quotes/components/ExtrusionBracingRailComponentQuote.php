<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionBracingRailComponentQuote
 */
class ExtrusionBracingRailComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Over 2m upto 3m requires a bracing rail.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // width and height are 2-3m
        if ($size['width'] > 2000 && $size['width'] <= 3000 && $size['height'] > 2000 && $size['height'] <= 3000) {
            return $unitQuantity;
        }
        return 0;
    }

}