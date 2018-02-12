<?php

namespace app\components\quotes\items;

use app\models\Item;
use app\models\Option;
use Yii;

/**
 * CafeBarrierItemQuote
 */
class CafeBarrierItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $factor = 0.75;
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
        return Yii::t('app', 'CafeBarrier factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}