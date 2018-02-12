<?php

namespace app\components\quotes\components;

use app\models\Item;
use app\models\Option;
use app\models\ProductToOption;
use Yii;

/**
 * FinishingComponentQuote
 */
class FinishingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the size of the sides that are selected.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        if (!is_array($options)) {
            return 0;
        }
        $unitQuantity = $item->quantity * $item->product->quantity;
        if (!isset($options['position'])) {
            $options['position'] = [0, 1, 2, 3]; // all sides
        }
        $position = $options['position'];
        if (empty($position)) {
            return 0;
        }
        $size = $this->getSize($item);
        if (!$size) {
            return 0;
        }
        $perimeter = 0;
        if (in_array(0, $position)) {
            $perimeter += $size['width'] / 1000;
        }
        if (in_array(1, $position)) {
            $perimeter += $size['width'] / 1000;
        }
        if (in_array(2, $position)) {
            $perimeter += $size['height'] / 1000;
        }
        if (in_array(3, $position)) {
            $perimeter += $size['height'] / 1000;
        }
        return $unitQuantity * $perimeter;
    }

}