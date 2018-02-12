<?php

namespace app\components\quotes\components;

use Yii;

/**
 * OctawallHorizontalBracingComponentQuote
 */
class OctawallHorizontalBracingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Under 1.5m use no bracing. Over 1.5m upto 3m use crossbars, others need bracing every 1.5m attached to the longer side.');
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

        // width and height are under 1.5m
        if ($size['width'] <= 1500 && $size['height'] <= 1500) {
            return 0;
        }

        // width and height are 1.5-3m - cross bracing
        if ($size['width'] > 1500 && $size['width'] <= 3000 && $size['height'] > 1500 && $size['height'] <= 3000) {
            return $unitQuantity * (($size['width'] / 1000) - ($extrusionOffset / 1000 * 3));
        }

        // width longer - vertical bracing only
        if ($size['width'] >= $size['height']) {
            return 0;
        }

        // height over 1.5m - horizontal bracing
        return $unitQuantity * (($size['width'] - $extrusionOffset) / 1000) * $this->getBracingQuantity($size['height']);
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getBracingQuantity($size)
    {
        $joinSize = 1500;
        return $size > $joinSize ? ceil(($size - $joinSize) / $joinSize) : 0;
    }

}