<?php

namespace app\components\quotes\items;

use app\models\Item;
use Yii;

/**
 * LedKitItemQuote
 */
class LedKitItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 1.5;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'LED Kit factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}