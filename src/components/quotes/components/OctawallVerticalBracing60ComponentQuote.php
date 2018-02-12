<?php

namespace app\components\quotes\components;

use Yii;

/**
 * OctawallVerticalBracing60ComponentQuote
 */
class OctawallVerticalBracing60ComponentQuote extends BaseComponentQuote
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
        $extrusionOffset = 60 * 2; // mm

        // width and height are under 1.5m
        if ($size['width'] <= 1500 && $size['height'] <= 1500) {
            return 0;
        }

        // width and height are 1.5-3m
        if ($size['width'] > 1500 && $size['width'] <= 3000 && $size['height'] > 1500 && $size['height'] <= 3000) {
            return $unitQuantity * (($size['height'] / 1000) - ($extrusionOffset / 1000 * 2));
        }

        // height longer - horizontal bracing only
        if ($size['height'] >= $size['width']) {
            return 0;
        }

        // width over 1.5m - vertical bracing
        return $unitQuantity * (($size['height'] - $extrusionOffset) / 1000) * $this->getBracingQuantity($size['width']);
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