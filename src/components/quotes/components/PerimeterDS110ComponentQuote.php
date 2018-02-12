<?php

namespace app\components\quotes\components;

use Yii;

/**
 * PerimeterDS110ComponentQuote
 */
class PerimeterDS110ComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the perimeter rounded to an integer, minus the corner bracket size.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $perimeter = $this->getPerimeter($item);
        // remove the corner bracket
        $perimeter = $perimeter - (500 * 8);
        if ($perimeter < 0) {
            $perimeter = 0;
        }
        return $unitQuantity * ceil($perimeter / 1000);
    }

}