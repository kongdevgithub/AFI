<?php

namespace app\components\quotes\items;

use app\models\Item;
use Yii;

/**
 * MarqueeItemQuote
 */
class MarqueeItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $factor = 1;
        if ($item && $item->isEmPrint()) {
            $factor = $factor * 0.7;
        }
        return $factor;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Marquee factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}