<?php

namespace app\components\quotes\items;

use app\models\Component;
use app\models\Item;
use app\models\Option;
use Yii;

/**
 * OctawallItemQuote
 */
class OctawallItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Octawall factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }

}