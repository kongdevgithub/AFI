<?php

namespace app\components\quotes\components;

use Yii;

/**
 * OctawallBracingLockComponentQuote
 */
class OctawallBracingLockComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Each section of bracing requires 2 tension locks.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // width and height are under 1.5m
        if ($size['width'] <= 1500 && $size['height'] <= 1500) {
            return 0;
        }

        // width and height are 1.5-3m
        if ($size['width'] > 1500 && $size['width'] <= 3000 && $size['height'] > 1500 && $size['height'] <= 3000) {
            return $unitQuantity * 6;
        }

        // width or height over 2m
        return $unitQuantity * $this->getBracingQuantity(max($size));
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getBracingQuantity($size)
    {
        $joinSize = 1500;
        return $size > $joinSize ? ceil($size / $joinSize) : 0;
    }

}