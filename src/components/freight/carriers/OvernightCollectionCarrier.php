<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * OvernightCollectionCarrier
 */
class OvernightCollectionCarrier extends Component
{

    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'Overnight First Class - Collection');
    }

    /**
     * @param Address $address
     * @param $boxes
     * @return array|bool
     */
    public static function getFreight($address, $boxes)
    {
        if ($address && strtoupper($address->country) != 'AUSTRALIA') {
            return false;
        }
        return [
            'name' => static::getName(),
            'zone' => $address ? $address->postcode : 'UNKNOWN',
            'weight' => 0,
            'cost' => 0,
            'price' => 0,
            'quote' => true,
        ];
    }

}