<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionJoiningDS110ComponentQuote
 */
class ExtrusionJoiningDS110ComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'One pack required every 3m of perimeter.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);
        return $unitQuantity * ($this->getJoinQuantity($size['width'] - 1000) + $this->getJoinQuantity($size['height'] - 1000));
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getJoinQuantity($size)
    {
        $joinSize = 3000;
        if ($size <= 0) {
            return 1;
        }
        return ceil($size / $joinSize) + 1;
    }

}