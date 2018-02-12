<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * RepCarrier
 */
class RepCarrier extends Component
{
    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'Rep to Deliver');
    }

    /**
     * @param Address $address
     * @param $boxes
     * @return array|bool
     */
    public static function getFreight($address, $boxes)
    {
        return [
            'name' => static::getName(),
            'zone' => $address ? strtoupper($address->city) : 'UNKNOWN',
            'weight' => 0,
            'cost' => 0,
            'price' => 20,
            'quote' => false,
        ];
    }

}