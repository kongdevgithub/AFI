<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionBracingComponentQuote
 */
class ExtrusionBracingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Under 2m use no bracing. Over 2m upto 3m use crossbars, others need bracing every 2m attached to the longer side.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // bracing fits inside the extrusion
        $extrusionOffset = 40 * 2; // mm

        // width and height are under 2m
        if ($size['width'] <= 2000 && $size['height'] <= 2000) {
            return 0;
        }

        // width and height are 2-3m
        if ($size['width'] > 2000 && $size['width'] <= 3000 && $size['height'] > 2000 && $size['height'] <= 3000) {
            return $unitQuantity * (($size['width'] / 1000) + ($size['height'] / 1000) - ($extrusionOffset / 1000 * 2));
        }

        // width or height over 2m
        $maxSize = max($size);
        $minSize = min($size);
        return $unitQuantity * (($minSize - $extrusionOffset) / 1000) * $this->getBracingQuantity($maxSize);
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getBracingQuantity($size)
    {
        $joinSize = 2000;
        return $size > $joinSize ? ceil(($size - $joinSize) / $joinSize) : 0;
    }

}