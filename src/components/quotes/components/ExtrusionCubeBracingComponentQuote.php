<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionCubeBracingComponentQuote
 */
class ExtrusionCubeBracingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Under 2m use no bracing. Faces with a side over 2m need bracing.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        // bracing fits inside the extrusion
        $extrusionOffset = 15.5 * 2; // mm

        // width and height are under 2m
        if ($size['width'] <= 2000 && $size['height'] <= 2000 && $size['depth'] <= 2000) {
            return 0;
        }

        // width or height over 2m
        // work out how many faces to brace
        $quantity = 0;
        $quantity += $this->getBracingLength(['width' => $size['width'], 'height' => $size['height']], $extrusionOffset) * 2; // front/back
        $quantity += $this->getBracingLength(['width' => $size['width'], 'height' => $size['depth']], $extrusionOffset) * 2; // top/bottom
        $quantity += $this->getBracingLength(['width' => $size['depth'], 'height' => $size['height']], $extrusionOffset) * 2; // left/right

        return $unitQuantity * $quantity;
    }

    /**
     * @param array $size
     * @param float $extrusionOffset
     * @return int
     */
    protected function getBracingLength($size, $extrusionOffset)
    {
        if ($size['width'] <= 2000 && $size['height'] <= 2000) {
            return 0;
        }
        $maxSize = max($size['width'], $size['height']);
        $minSize = min($size['width'], $size['height']);
        return (($minSize - $extrusionOffset) / 1000) * $this->getBracingQuantity($maxSize);
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