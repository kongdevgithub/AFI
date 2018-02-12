<?php

namespace app\components\quotes\items;

use app\components\fields\BaseField;
use app\components\Helper;
use app\models\Item;
use app\models\Option;
use app\models\Size;
use Yii;

/**
 * IlluminatedItemQuote
 */
class IlluminatedItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return Helper::getAmountBetweenScale($this->getPerimeter($item), [
            '0' => 1.05,
            '4' => 1.2,
            '7' => 1.35,
            '10' => 1.35,
            '16' => 1.6,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        if ($item) {
            return Yii::t('app', 'Illuminated factor {factor} based on perimeter {perimeter}.', [
                'perimeter' => $this->getPerimeter($item),
                'factor' => $this->getQuoteFactor($item),
            ]);
        }
        return Yii::t('app', 'Illuminated factor (1-1.6) based on perimeter (0-16).');
    }

    /**
     * @param Item $item
     * @return float|string
     */
    protected function getPerimeter($item)
    {
        $size = $item->getSize();
        return ($size['width'] + $size['height']) * 2 / 1000;
    }
}