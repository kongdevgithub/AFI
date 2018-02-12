<?php

namespace app\components\quotes\items;

use app\models\Item;
use app\models\Option;
use Yii;

/**
 * FlagItemQuote
 */
class FlagItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $factor = 0.85;
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
        return Yii::t('app', 'Flag factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}