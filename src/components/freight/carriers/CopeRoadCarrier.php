<?php

namespace app\components\freight\carriers;

use app\components\Csv;
use app\models\Address;
use Yii;
use yii\base\Component;

/**
 * CopeRoadCarrier
 */
class CopeRoadCarrier extends Component
{

    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::t('app', 'Road Freight');
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
        $weight = ceil(static::getWeight($boxes));
        $type = static::getType($weight);
        $rate = $address ? static::getRate($address->postcode, $type) : 0;
        $cost = $rate ? static::getCost($rate, $weight) : 0;
        return [
            'name' => static::getName() . ' ' . ucwords($type),
            'zone' => $address ? static::getZone($address->postcode, $type) : 'UNKNOWN',
            'weight' => $weight,
            'cost' => $cost ? $cost : 0,
            'quote' => $cost > 0 && $cost <= 150 ? false : true,
        ];
    }

    /**
     * @param $rate
     * @param $weight
     * @return float
     */
    private static function getCost($rate, $weight)
    {
        $cost = max($rate['base'] + ($rate['kg'] * $weight), $rate['min']);
        $cost += $cost * 0.1173; // 11.73% FUEL LEVY
        return $cost;
    }

    /**
     * @param $weight
     * @return bool|string
     */
    private static function getType($weight)
    {
        return $weight <= 30 ? 'normal' : 'tailgate';
    }

    /**
     * @param $boxes
     * @return float
     */
    private static function getWeight($boxes)
    {
        $cubicMultiplier = 225;
        $deadWeight = 0;
        foreach ($boxes as $box) {
            $cubicWeight = $box['width'] / 100 * $box['length'] / 100 * $box['height'] / 100 * $cubicMultiplier;
            $deadWeight += max($cubicWeight, $box['dead_weight'], $box['cubic_weight']);
        }
        return ceil($deadWeight);
    }

    /**
     * @param $postcode
     * @param string $type
     * @return bool|array
     */
    private static function getRate($postcode, $type)
    {
        $zone = static::getZone($postcode, $type);
        if (!$zone) {
            return false;
        }
        $rates = Csv::csvToArray(Yii::getAlias('@data/freight/cope/rates_' . $type . '.csv'));
        foreach ($rates as $rate) {
            if (trim($rate['zone']) == $zone) {
                return $rate;
            }
        }
        return false;
    }

    /**
     * @param $postcode
     * @return string
     */
    private static function getZone($postcode, $type)
    {
        $postcode = (int)$postcode;
        $zones = Csv::csvToArray(Yii::getAlias('@data/freight/cope/zones_' . $type . '.csv'));
        foreach ($zones as $zone) {
            if ((int)$zone['postcode'] == $postcode) {
                return trim($zone['zone']);
            }
        }
        return false;
    }

}