<?php

namespace app\components\quotes\items;

use app\components\Helper;
use app\models\Item;
use app\models\Option;
use Yii;

/**
 * BannerItemQuote
 */
class BannerItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $size = $item->getSize();
        $area = $size['width'] * $size['height'] / 1000 / 1000;
        $factor = Helper::getAmountBetweenScale($area, [
            '0' => 1.2,
            '9' => 0.65,
        ]);
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
        if ($item) {
            return Yii::t('app', 'Banner factor {factor}.', [
                'factor' => $this->getQuoteFactor($item),
            ]);
        }
        return Yii::t('app', 'Banner factor {factor}.', [
            'factor' => '1.2-0.65 based on area 9-0 m2',
        ]);
    }
}