<?php

namespace app\components\quotes\items;

use app\components\fields\BaseField;
use app\components\Helper;
use app\models\Item;
use app\models\Option;
use app\models\Size;
use Yii;

/**
 * MagniprintItemQuote
 */
class MagniprintItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 0.9;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Magniprint factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }

}