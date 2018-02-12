<?php

namespace app\components\quotes\items;

use app\models\Item;
use app\models\Option;
use Yii;

/**
 * BespokeItemQuote
 */
class BespokeItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 1.4;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Bespoke factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }
}