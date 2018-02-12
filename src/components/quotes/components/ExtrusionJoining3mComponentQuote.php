<?php

namespace app\components\quotes\components;

use Yii;

/**
 * ExtrusionJoiningComponentQuote
 */
class ExtrusionJoining3mComponentQuote extends ExtrusionJoiningComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'On W/H; one pack for every 3m+ of perimeter.');
    }

    /**
     * @param float $size
     * @return int
     */
    protected function getJoinQuantity($size)
    {
        $joinSize = 3000;
        return $size > $joinSize ? ceil(($size - $joinSize) / $joinSize) : 0;
    }

}