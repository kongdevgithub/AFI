<?php

namespace app\components\quotes\items;

use app\components\fields\BaseField;
use app\components\Helper;
use app\models\Item;
use app\models\ItemType;
use app\models\Option;
use app\models\Size;
use Yii;

/**
 * ReframeItemQuote
 */
class ReframeItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        if ($item->isBlankPrint()) {
            return Helper::getAmountBetweenScale($this->getPerimeter($item), [
                '0' => 1.05,
                '8' => 1.05,
                '10' => 1.1,
                '13' => 1.2,
            ]);
        }
        return Helper::getAmountBetweenScale($this->getPerimeter($item), [
            '0' => 1.05,
            '8' => 1.05,
            '10' => 1.1,
            '16' => 1.6,
        ]);
    }

    /**
     * @param Item $item
     * @return float|string
     */
    public function getDescription($item = null)
    {
        if ($item) {
            if ($item->isBlankPrint()) {
                return Yii::t('app', 'Reframe factor {factor} based on blank print.', [
                    'factor' => $this->getQuoteFactor($item),
                ]);
            }
            return Yii::t('app', 'Reframe factor {factor} based on perimeter {perimeter}.', [
                'perimeter' => $this->getPerimeter($item),
                'factor' => $this->getQuoteFactor($item),
            ]);
        }
        return Yii::t('app', 'Reframe factor (1.05-1.6) based on perimeter (8-16m).');
    }

    /**
     * @param Item $item
     * @return float|string
     */
    protected function getPerimeter($item)
    {
        $size = $item->getSize();
        if (!$size || empty($size['width']) || empty($size['height'])) {
            return 0;
        }
        return ($size['width'] + $size['height']) * 2 / 1000;
    }
}