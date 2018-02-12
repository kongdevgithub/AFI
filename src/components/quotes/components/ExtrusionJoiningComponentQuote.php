<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionJoiningComponentQuote
 */
class ExtrusionJoiningComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'On W/H; Two packs required, plus additional pack every 1m.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);
        if (!isset($size['width']) || !isset($size['height'])) {
            return 0;
        }
        $joinQuantity = ($this->getJoinQuantity($size['width']) + $this->getJoinQuantity($size['height']));
        if (isset($size['depth'])) {
            $joinQuantity += $this->getJoinQuantity($size['depth']);
            $joinQuantity *= 2;
        }
        return $unitQuantity * $joinQuantity;
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getJoinQuantity($size)
    {
        $joinSize = 1000;
        return ceil($size / $joinSize) + 1;
    }

}