<?php

namespace app\components\freight;

use app\components\freight\boxes\ExtrusionBox;
use app\components\freight\boxes\HardwareBox;
use app\components\freight\boxes\PrintBox;
use app\components\freight\carriers\ClientCarrier;
use app\components\freight\carriers\CopeRoadCarrier;
use app\components\freight\carriers\InstallerCarrier;
use app\components\freight\carriers\InternationalCarrier;
use app\components\freight\carriers\OvernightCarrier;
use app\components\freight\carriers\OvernightCollectionCarrier;
use app\components\freight\carriers\RepCarrier;
use app\components\freight\carriers\RoadCollectionCarrier;
use app\components\freight\carriers\SameDayCarrier;
use app\components\freight\carriers\SameDayLocalCollectionCarrier;
use app\components\freight\carriers\SameDayLocalVipCarrier;
use app\components\freight\carriers\SiteToSiteCarrier;
use app\components\freight\carriers\SiteToSiteOutOfHoursCarrier;
use app\components\freight\carriers\SiteToSitePickupCarrier;
use app\components\freight\carriers\SiteToSiteSaturdayCarrier;
use app\components\freight\carriers\SwiftCarrier;
use app\components\Helper;
use app\models\Address;
use app\models\Job;
use yii\base\Component;

/**
 * BaseFreight
 */
class Freight extends Component
{

    /**
     * @param Job $job
     * @param array $boxes
     * @return array
     */
    public static function getBoxes($job, $boxes = [])
    {
        $boxes = ExtrusionBox::getBoxes($job, $boxes);
        $boxes = PrintBox::getBoxes($job, $boxes);
        $boxes = HardwareBox::getBoxes($job, $boxes);
        return $boxes;
    }

    /**
     * @param Job $job
     * @param array $boxes
     * @return array
     */
    public static function getUnboxed($job, $boxes)
    {
        $unboxed = [];
        foreach ($job->products as $product) {
            foreach ($product->items as $item) {
                $quantity = $item->quantity * $product->quantity;
                if ($quantity == 0) continue;
                $inBox = false;
                foreach ($boxes as $box) {
                    foreach ($box['items'] as $type => $items) {
                        if (isset($items[$item->id])) {
                            $inBox = true;
                            break(2);
                        }
                    }
                }
                if (!$inBox) {
                    $unboxed[$item->id] = $quantity;
                }
            }
        }
        return $unboxed;
    }

    /**
     * @param Address $address
     * @param array $boxes
     * @return array
     */
    public static function getCarrierFreight($address, $boxes)
    {
        $carriers = [];
        foreach (static::getCarrierClasses() as $method => $carrierClass) {
            $freight = $carrierClass::getFreight($address, $boxes);
            if ($freight === false) continue;
            if (!isset($freight['price'])) {
                $freight['price'] = static::getPrice($freight);
            }
            $freight['method'] = $method;
            $carriers[$method] = $freight;
        }
        return $carriers;
    }

    public static function getCarrierNames()
    {
        $carriers = [];
        foreach (static::getCarrierClasses() as $carrierName => $carrierClass) {
            $carriers[$carrierName] = $carrierClass::getName();
        }
        return $carriers;
    }

    public static function getCarrierClasses()
    {
        return [
            'cope-road' => CopeRoadCarrier::className(),
            'swift' => SwiftCarrier::className(),
            'same-day' => SameDayCarrier::className(),
            'same-day-local-vip' => SameDayLocalVipCarrier::className(),
            'same-day-local-collection' => SameDayLocalCollectionCarrier::className(),
            'site-to-site' => SiteToSiteCarrier::className(),
            'site-to-site-saturday' => SiteToSiteSaturdayCarrier::className(),
            'site-to-site-pickup' => SiteToSitePickupCarrier::className(),
            'site-to-site-out-of-hours' => SiteToSiteOutOfHoursCarrier::className(),
            'road-collection' => RoadCollectionCarrier::className(),
            'overnight' => OvernightCarrier::className(),
            'overnight-collection' => OvernightCollectionCarrier::className(),
            'international' => InternationalCarrier::className(),
            'rep' => RepCarrier::className(),
            'installer' => InstallerCarrier::className(),
            'client' => ClientCarrier::className(),
        ];
    }

    /**
     * @param array $boxes
     * @return float
     */
    public static function getWeight($boxes)
    {
        $weight = 0;
        foreach ($boxes as $box) {
            $weight += $box['weight'];
        }
        return $weight;
    }

    /**
     * Add 75% margin on costs and round up to the nearest $5
     *
     * @param array $carrier
     * @return float
     */
    public static function getPrice($carrier)
    {
        // max(31.592*weight^(-0.816)*weight,cost*2.5-50,35)
        $price = max($carrier['cost'] * 2.5 - 50, 35, $carrier['cost'] * 2 / 3);
        if ($carrier['weight']) {
            $weightPrice = pow(31.592 * $carrier['weight'], -0.816 * $carrier['weight']);
            $price = max($weightPrice, $price);
        }
        return static::roundUpToAny($price, 5);
    }

    /**
     * Behaviour: 50 outputs 50, 52 outputs 55, 50.25 outputs 55
     * @see https://stackoverflow.com/a/4133893/599477
     *
     * @param $n
     * @param int $x
     * @return float
     */
    private static function roundUpToAny($n, $x = 5)
    {
        return (ceil($n) % $x === 0) ? ceil($n) : round(($n + $x / 2) / $x) * $x;
    }

}