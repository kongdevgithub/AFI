<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * SwiftCarrier
 */
class SwiftCarrier extends Component
{
    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'Same Day - Local');
    }

    /**
     * @param Address $address
     * @param $boxes
     * @return bool|array
     */
    public static function getFreight($address, $boxes)
    {
        if ($address && (strtoupper($address->country) != 'AUSTRALIA' || strtoupper($address->state) != 'VIC')) {
            return false;
        }
        $weight = ceil(static::getWeight($boxes));
        $type = static::getType($weight, $boxes);
        $cost = 0;
        if ($type) {
            $rate = $address ? static::getRate($address->city) : false;
            $cost = ($rate && isset($rate[$type])) ? $rate[$type] : 0;
        }
        return [
            'name' => static::getName() . ($type ? ' ' . $type : ''),
            'zone' => $address ? strtoupper($address->city) : 'UNKNOWN',
            'weight' => $weight,
            'cost' => $cost ? $cost : 0,
            'quote' => $cost > 0 && $cost <= 150 && $weight <= 150 ? false : true,
        ];
    }

    /**
     * @param $weight
     * @param $boxes
     * @return bool|string
     */
    private static function getType($weight, $boxes)
    {
        if ($weight > 1000) {
            return false;
        }
        $longest = 0;
        foreach ($boxes as $box) {
            foreach ($box['pieces'] as $piece) {
                $longest = max($longest, $piece['width'], $piece['length'], $piece['height']);
            }
        }
        if ($longest <= 270) {
            return '1S';
        }
        if ($weight <= 100) {
            return 'RR';
        }
        if ($weight <= 150) {
            return 'RT';
        }
        return false;
    }

    /**
     * @param $boxes
     * @return float
     */
    private static function getWeight($boxes)
    {
        $weight = 0;
        foreach ($boxes as $box) {
            $weight += $box['dead_weight'];
        }
        return ceil($weight);
    }

    /**
     * @param $zone
     * @return bool|array
     */
    private static function getRate($zone)
    {
        $zone = trim(strtoupper($zone));
        if (!$zone) {
            return false;
        }
        $rates = Csv::csvToArray(Yii::getAlias('@data/freight/swift/rates.csv'));
        foreach ($rates as $rate) {
            if (trim(strtoupper($rate['ZONE'])) == $zone) {
                return $rate;
            }
        }
        return false;
    }

}