<?php

namespace app\components\quotes\items;

use app\models\Item;
use Yii;

/**
 * WallpaperItemQuote
 */
class WallpaperItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 0.85;
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Wallpaper factor {factor}.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }

}