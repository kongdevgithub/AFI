<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * ClientCarrier
 */
class ClientCarrier extends Component
{

    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'Client to Collect');
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
            'zone' => 'AFI',
            'weight' => 0,
            'cost' => 0,
            'price' => 0,
            'quote' => false,
        ];
    }

}