<?php

namespace app\components\quotes\items;

use app\components\fields\BaseField;
use app\components\Helper;
use app\models\Item;
use app\models\Option;
use app\models\Size;
use Yii;

/**
 * StretchedCanvasItemQuote
 */
class StretchedCanvasItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 1.2;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'StretchedCanvas factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }

}