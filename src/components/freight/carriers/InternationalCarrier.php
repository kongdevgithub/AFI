<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * InternationalCarrier
 */
class InternationalCarrier extends Component
{

    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'International Air');
    }

    /**
     * @param Address $address
     * @param $boxes
     * @return array|bool
     */
    public static function getFreight($address, $boxes)
    {
        if ($address && strtoupper($address->country) == 'AUSTRALIA') {
            return false;
        }
        return [
            'name' => static::getName(),
            'zone' => $address ? strtoupper($address->country) : 'UNKNOWN',
            'weight' => 0,
            'cost' => 0,
            'price' => 0,
            'quote' => true,
        ];
    }

}