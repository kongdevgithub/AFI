<?php

namespace app\components\quotes\items;

use app\components\fields\BaseField;
use app\components\Helper;
use app\models\Item;
use app\models\Option;
use app\models\Size;
use Yii;

/**
 * PopupAFrameItemQuote
 */
class PopupAFrameItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        $factor = 0.78;
        if ($this->getWidth($item) == 1300) {
            $factor = 0.65;
        }
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
            return Yii::t('app', 'PopupAFrame factor {factor} based on width {width}.', [
                'factor' => $this->getQuoteFactor($item),
                'width' => $this->getWidth($item),
            ]);
        }
        return Yii::t('app', 'PopupAFrame factor 0.65 or 0.78  based on width 1300W=0.65.');
    }

    /**
     * @param Item $item
     * @return float|string
     */
    protected function getWidth($item)
    {
        $size = $item->getSize();
        return $size['width'];
    }
}