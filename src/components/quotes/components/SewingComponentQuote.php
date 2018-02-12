<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Item;
use Yii;

/**
 * SewingComponentQuote
 */
class SewingComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the perimeter.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $perimeter = $this->getPerimeter($item);
        return $unitQuantity * $perimeter;
    }

}