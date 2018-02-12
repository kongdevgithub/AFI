<?php

namespace app\components\freight\boxes;

use app\models\Job;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * HardwareBox
 */
class HardwareBox extends Component
{
    /**
     *
     */
    const HARDWARE_WEIGHT_FACTOR = 1.1;

    /**
     * @param Job $job
     * @param array $boxes
     * @return array
     */
    public static function getBoxes($job, $boxes = [])
    {
        $hardwareBox = [
            'type' => 'Hardware',
            'width' => 0,
            'length' => 0,
            'height' => 0,
            'cubic_weight' => 0,
            'dead_weight' => 0,
            'pieces' => [],
            'products' => ['hardware' => []],
            'items' => ['hardware' => []],
            'quantity' => [
                'hardware' => 0,
            ],
        ];

        foreach ($job->products as $product) {
            foreach ($product->items as $item) {
                $quantity = $item->quantity * $product->quantity;
                if (!$quantity) {
                    continue;
                }
                $materials = $item->getMaterials();
                if (!$materials) {
                    continue;
                }

                foreach ($materials as $k => $material) {
                    if ($material['total_cubic_weight'] == 0) {
                        unset($materials[$k]);
                        continue;
                    }
                    foreach ($boxes as $box) {
                        foreach ($box['pieces'] as $piece) {
                            if ($piece['id'] == $material['id']) {
                                unset($materials[$k]);
                                continue(3);
                            }
                        }
                    }
                }

                if ($materials) {
                    for ($i = 0; $i < $quantity; $i++) {
                        foreach ($materials as $material) {
                            $hardwareBox['dead_weight'] += $material['total_dead_weight'] / $quantity;
                            $hardwareBox['cubic_weight'] += ($material['total_cubic_weight'] / $quantity) * static::HARDWARE_WEIGHT_FACTOR;
                            $hardwareBox['pieces'][] = [
                                'id' => $material['id'],
                                'code' => $material['code'],
                                'length' => 0,
                                'width' => 0,
                                'height' => 0,
                                'dead_weight' => $material['total_dead_weight'] / $quantity,
                                'cubic_weight' => ($material['total_cubic_weight'] / $quantity) * static::HARDWARE_WEIGHT_FACTOR,
                            ];
                        }
                        $hardwareBox['products']['hardware'][$product->id] = $product->id;
                        $hardwareBox['items']['hardware'][$item->id] = $item->id;
                        $hardwareBox['quantity']['hardware']++;
                    }
                }
            }
        }

        if ($hardwareBox['quantity']['hardware']) {
            return ArrayHelper::merge($boxes, [$hardwareBox]);
        }
        return $boxes;
    }
}