<?php

namespace app\components\freight\boxes;

use app\models\Component;
use app\models\Item;
use app\models\Job;
use app\models\Option;
use app\models\Product;
use app\models\ProductToOption;
use yii\helpers\ArrayHelper;

/**
 * PrintBox
 */
class PrintBox extends \yii\base\Component
{
    const SATCHEL_MAX_WEIGHT = 5;
    const SATCHEL_WIDTH = 30;
    const SATCHEL_LENGTH = 50;
    const SATCHEL_HEIGHT = 5;
    const SATCHEL_WEIGHT_FACTOR = 1.5;

    const EXTRUSION_BOX_HEIGHT = 34;

    /**
     * @param Job $job
     * @param array $boxes
     * @return array
     */
    public static function getBoxes($job, $boxes = [])
    {
        $printBoxes = [];

        foreach ($job->products as $product) {
            foreach ($product->items as $item) {
                $quantity = $item->quantity * $product->quantity;
                if (!$quantity) {
                    continue;
                }
                $productToOption = static::getProductToOption($item);
                if (!$productToOption) {
                    continue;
                }
                $component = static::getComponent($productToOption);
                if (!$component) {
                    continue;
                }
                $size = $item->getSize();
                if (!$size || !isset($size['width'])) {
                    continue;
                }
                $area = $item->getArea() / $quantity;
                if (!$area) {
                    continue;
                }
                $deadWeight = $component->unit_dead_weight * $area;
                $cubicWeight = $component->unit_cubic_weight * $area;
                if (static::getBackComponent($item)) {
                    $deadWeight *= 2;
                    $cubicWeight *= 2;
                }
                $length = ceil(min($size) / 10);
                $pieces = static::getPieces($productToOption, $component, $length, $area);
                $allowFold = static::allowFold($component);

                for ($i = 0; $i < $quantity; $i++) {

                    // merge into matching extrusion box, or make a new box
                    $mergeKey = static::getMergeKey($boxes, $product);
                    if ($mergeKey !== false) {
                        if ($boxes[$mergeKey]['height'] == ExtrusionBox::BOX_HEIGHT) {
                            $boxes[$mergeKey]['height'] = static::EXTRUSION_BOX_HEIGHT;
                        }
                        $boxes[$mergeKey]['dead_weight'] += round($deadWeight, 3);
                        $boxes[$mergeKey]['cubic_weight'] += round($cubicWeight * static::SATCHEL_WEIGHT_FACTOR, 3);
                        $boxes[$mergeKey]['pieces'] = ArrayHelper::merge($boxes[$mergeKey]['pieces'], $pieces);
                        $boxes[$mergeKey]['products']['print'][$product->id] = $product->id;
                        $boxes[$mergeKey]['items']['print'][$item->id] = $item->id;
                        if (!isset($boxes[$mergeKey]['quantity']['print'])) {
                            $boxes[$mergeKey]['quantity']['print'] = 0;
                        }
                        $boxes[$mergeKey]['quantity']['print']++;
                    } else {
                        // merge into last print boxes if weight allows, or make a new box
                        $merged = false;
                        foreach ($printBoxes as $k => $printBox) {
                            if (!$allowFold || $printBoxes[$k]['dead_weight'] + $deadWeight < static::SATCHEL_MAX_WEIGHT) {
                                $printBoxes[$k]['dead_weight'] += round($deadWeight, 3);
                                $printBoxes[$k]['cubic_weight'] += round($cubicWeight * static::SATCHEL_WEIGHT_FACTOR, 3);
                                $printBoxes[$k]['pieces'] = ArrayHelper::merge($printBox['pieces'], $pieces);
                                $printBoxes[$k]['products']['print'][$product->id] = $product->id;
                                $printBoxes[$k]['items']['print'][$item->id] = $item->id;
                                $printBoxes[$k]['quantity']['print']++;
                                $merged = true;
                                break;
                            }
                        }
                        if (!$merged) {
                            $printBox = [
                                'type' => 'Print',
                                'width' => static::SATCHEL_WIDTH,
                                'length' => static::SATCHEL_LENGTH,
                                'height' => static::SATCHEL_HEIGHT,
                                'dead_weight' => round($deadWeight, 3),
                                'cubic_weight' => round($cubicWeight * static::SATCHEL_WEIGHT_FACTOR, 3),
                                'pieces' => $pieces,
                                'products' => ['print' => [$product->id => $product->id]],
                                'items' => ['print' => [$item->id => $item->id]],
                                'quantity' => [
                                    'print' => 1,
                                ],
                            ];
                            if ($deadWeight > static::SATCHEL_MAX_WEIGHT || !$allowFold) {
                                $printBox['type'] = 'Print-Roll';
                                $printBox['width'] = ExtrusionBox::BOX_WIDTH;
                                $printBox['length'] = $length + ExtrusionBox::BOX_LENGTH_OFFSET;
                                $printBox['height'] = ExtrusionBox::BOX_HEIGHT;
                            }
                            $printBoxes[] = $printBox;
                        }
                    }

                }

            }
        }

        return ArrayHelper::merge($boxes, $printBoxes);
    }

    /**
     * @param $boxes
     * @param Product $product
     * @return bool|int|string
     */
    private static function getMergeKey($boxes, $product)
    {
        // find an extrusion box to merge into
        foreach ($boxes as $k => $box) {
            if (!in_array($box['type'], ['Extrusion', 'Extrusion-Single', 'Extrusion-Flat'])) continue;
            if (!isset($box['products']['extrusion'][$product->id])) continue;
            if (isset($box['quantity']['print']) && $box['quantity']['extrusion'] - $box['quantity']['print'] < 1) continue;
            return $k;
        }
        // try without the print quantity check
        foreach ($boxes as $k => $box) {
            if (!in_array($box['type'], ['Extrusion', 'Extrusion-Single', 'Extrusion-Flat'])) continue;
            if (!isset($box['products']['extrusion'][$product->id])) continue;
            return $k;
        }
        // try without the product check
        foreach ($boxes as $k => $box) {
            if (!in_array($box['type'], ['Extrusion', 'Extrusion-Single', 'Extrusion-Flat'])) continue;
            if (isset($box['quantity']['print']) && $box['quantity']['extrusion'] - $box['quantity']['print'] < 1) continue;
            return $k;
        }
        // find any extrusion box
        foreach ($boxes as $k => $box) {
            if (!in_array($box['type'], ['Extrusion', 'Extrusion-Single', 'Extrusion-Flat'])) continue;
            return $k;
        }
        return false;
    }

    /**
     * @param ProductToOption $productToOption
     * @param Component $component
     * @param $length
     * @param $area
     * @return array
     */
    public static function getPieces($productToOption, $component, $length, $area)
    {
        return [
            [
                'id' => 'P2O-' . $productToOption->id,
                'code' => $component->code,
                'length' => $length,
                'width' => 0,
                'height' => 0,
                'dead_weight' => round($component->unit_dead_weight * $area, 3),
                'cubic_weight' => round($component->unit_cubic_weight * $area, 3),
            ],
        ];
    }

    /**
     * @param Component $component
     * @return bool
     */
    private static function allowFold($component)
    {
        $config = $component->getConfigDecoded();
        if (!empty($config['allow_fold'])) {
            return true;
        }
        return false;
    }

    /**
     * @param Item $item
     * @return bool|ProductToOption
     */
    private static function getProductToOption($item)
    {
        $productToOption = $item->getProductToOption(Option::OPTION_SUBSTRATE);
        if (!$productToOption) {
            return false;
        }
        return $productToOption;
    }

    /**
     * @param ProductToOption $productToOption
     * @return bool|Component
     */
    private static function getComponent($productToOption)
    {
        $component_id = $productToOption->valueDecoded;
        if (!$component_id) {
            return false;
        }
        $component = Component::findOne($component_id);
        if (!$component) {
            return false;
        }
        return $component;
    }

    /**
     * @param Item $item
     * @return bool|Component
     */
    private static function getBackComponent($item)
    {
        $productToOption = $item->getProductToOption(Option::OPTION_SUBSTRATE_BACK);
        if (!$productToOption) {
            return false;
        }
        $component_id = $productToOption->valueDecoded;
        if (!$component_id) {
            return false;
        }
        $component = Component::findOne($component_id);
        if (!$component) {
            return false;
        }
        return $component;
    }

}