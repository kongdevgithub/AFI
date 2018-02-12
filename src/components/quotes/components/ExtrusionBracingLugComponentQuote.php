<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionBracingLugComponentQuote
 */
class ExtrusionBracingLugComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Each section of bracing requires a lug pack.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // width and height are under 2m
        if ($size['width'] <= 2000 && $size['height'] <= 2000) {
            return 0;
        }

        // width and height are 2-3m
        if ($size['width'] > 2000 && $size['width'] <= 3000 && $size['height'] > 2000 && $size['height'] <= 3000) {
            return $unitQuantity * 2;
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
        $joinSize = 2000;
        return $size > $joinSize ? ceil(($size - $joinSize) / $joinSize) : 0;
    }

}