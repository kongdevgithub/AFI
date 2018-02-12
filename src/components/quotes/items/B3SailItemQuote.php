<?php

namespace app\components\quotes\items;

use app\models\Item;
use app\models\Option;
use Yii;

/**
 * B3SailItemQuote
 */
class B3SailItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $factor = 0.6;
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
        return Yii::t('app', 'B3Sail factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}