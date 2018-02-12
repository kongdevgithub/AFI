<?php

namespace app\components\freight\boxes;

use app\models\Component;
use app\models\Item;
use app\models\Job;
use app\models\Option;
use app\models\ProductToOption;
use yii\base\Component as BaseComponent;
use yii\helpers\ArrayHelper;

/**
 * ExtrusionBox
 */
class ExtrusionBox extends BaseComponent
{
    const BOX_MAX_WEIGHT = 22;
    const BOX_MAX_LENGTH = 350;
    const BOX_WIDTH = 17;
    const BOX_HEIGHT = 17;
    const BOX_LENGTH_OFFSET = 10;
    const BOX_WEIGHT_FACTOR = 1.1;

    /**
     * @param Job $job
     * @param array $boxes
     * @return array
     */
    public static function getBoxes($job, $boxes = [])
    {
        foreach (static::getExtrusionBoxes($job) as $box) {
            foreach (static::getSplitBoxes($box) as $_box) {
                $boxes[] = $_box;
            }
        }
        return $boxes;
    }

    /**
     * put extrusions into boxes
     *
     * @param Job $job
     * @return array
     */
    private static function getExtrusionBoxes($job)
    {
        $extrusionBoxes = [];
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
                $perimeter = $item->getPerimeter();
                if (!$perimeter) {
                    continue;
                }
                $weight = $component->unit_dead_weight * ($perimeter / $quantity);
                $pieces = static::getPieces($productToOption, $component, $size);
                $length = 0;
                foreach ($pieces as $piece) {
                    if ($length < $piece['length']) {
                        $length = $piece['length'];
                    }
                }
                $length += static::BOX_LENGTH_OFFSET;

                for ($i = 0; $i < $quantity; $i++) {
                    // merge into last boxes if weight allows, or make a new box
                    $merged = false;
                    $allowMerge = static::allowComponentMerge($component);
                    if ($allowMerge) {
                        foreach ($extrusionBoxes as $k => $extrusionBox) {
                            if ($extrusionBoxes[$k]['type'] == 'Extrusion' && $extrusionBoxes[$k]['dead_weight'] + $weight < static::BOX_MAX_WEIGHT) {
                                $extrusionBoxes[$k]['length'] = max($length, $extrusionBox['length']);
                                $extrusionBoxes[$k]['dead_weight'] += round($weight, 3);
                                $extrusionBoxes[$k]['cubic_weight'] += round($weight, 3);
                                $extrusionBoxes[$k]['pieces'] = ArrayHelper::merge($extrusionBox['pieces'], $pieces);
                                $extrusionBoxes[$k]['products']['extrusion'][$product->id] = $product->id;
                                $extrusionBoxes[$k]['items']['extrusion'][$item->id] = $item->id;
                                $extrusionBoxes[$k]['quantity']['extrusion']++;
                                $merged = true;
                                break;
                            }
                        }
                    }
                    if (!$merged) {
                        if (static::allowFlatten($component)) {
                            $extrusionBoxes[] = [
                                'type' => $allowMerge ? 'Extrusion' : 'Extrusion-Single',
                                'width' => static::BOX_WIDTH,
                                'length' => $length,
                                'height' => static::BOX_HEIGHT,
                                'dead_weight' => round($weight, 3),
                                'cubic_weight' => round($weight, 3),
                                'pieces' => $pieces,
                                'products' => ['extrusion' => [$product->id => $product->id]],
                                'items' => ['extrusion' => [$item->id => $item->id]],
                                'quantity' => [
                                    'extrusion' => 1,
                                ],
                            ];
                        } else {
                            $extrusionBoxes[] = [
                                'type' => 'Extrusion-Flat',
                                'width' => $pieces[0]['width'],
                                'length' => $pieces[0]['length'],
                                'height' => $pieces[0]['height'],
                                'cubic_weight' => round($weight, 3),
                                'dead_weight' => round($weight, 3),
                                'pieces' => $pieces,
                                'products' => ['extrusion' => [$product->id => $product->id]],
                                'items' => ['extrusion' => [$item->id => $item->id]],
                                'quantity' => [
                                    'extrusion' => 1,
                                ],
                            ];
                        }
                    }
                }
            }
        }
        return $extrusionBoxes;
    }

    /**
     * split boxes that are too heavy
     *
     * @param array $box
     * @return array
     */
    private static function getSplitBoxes($box)
    {
        if ($box['dead_weight'] <= static::BOX_MAX_WEIGHT) {
            return [$box];
        }

        $splitBoxes = [];

        $boxDeadWeight = 0;
        $boxCubicWeight = 0;
        $maxPieceLength = 0;
        $boxPieces = [];

        // for each of the pieces
        foreach ($box['pieces'] as $piece) {

            // if the box is full, package it up and reset
            if ($boxDeadWeight + $piece['dead_weight'] > static::BOX_MAX_WEIGHT) {
                $splitBoxes[] = [
                    'type' => 'Extrusion',
                    'width' => $box['width'],
                    'length' => $maxPieceLength + static::BOX_LENGTH_OFFSET,
                    'height' => $box['height'],
                    'dead_weight' => round($boxDeadWeight * static::BOX_WEIGHT_FACTOR, 3),
                    'cubic_weight' => round($boxCubicWeight, 3),
                    'pieces' => $boxPieces,
                    'products' => $box['products'],
                    'items' => $box['items'],
                    'quantity' => ArrayHelper::merge($box['quantity'], [
                        'extrusion' => 0,
                    ]),
                ];
                $boxPieces = [];
                $maxPieceLength = 0;
                $boxDeadWeight = 0;
                $boxCubicWeight = 0;
            }

            // add the piece to the box
            $boxPieces[] = $piece;
            $boxDeadWeight += $piece['dead_weight'];
            $boxCubicWeight += $piece['cubic_weight'];
            if ($maxPieceLength < $piece['length']) {
                $maxPieceLength = $piece['length'];
            }

        }

        // package up the last box
        $splitBoxes[] = [
            'type' => 'Extrusion',
            'width' => static::BOX_WIDTH,
            'length' => $maxPieceLength + static::BOX_LENGTH_OFFSET,
            'height' => static::BOX_HEIGHT,
            'dead_weight' => round($boxDeadWeight * static::BOX_WEIGHT_FACTOR, 3),
            'cubic_weight' => round($boxCubicWeight, 3),
            'pieces' => $boxPieces,
            'products' => $box['products'],
            'items' => $box['items'],
            'quantity' => ArrayHelper::merge($box['quantity'], [
                'extrusion' => 0,
            ]),
        ];

        // move the extrusion quantity to the first box
        $splitBoxes[0]['quantity']['extrusion'] = $box['quantity']['extrusion'] + 1;

        return $splitBoxes;
    }

    /**
     * @param ProductToOption $productToOption
     * @param Component $component
     * @param array $size
     * @return array
     */
    private static function getPieces($productToOption, $component, $size)
    {
        if (!static::allowFlatten($component)) {
            return [
                [
                    'id' => 'P2O-' . $productToOption->id,
                    'code' => $component->code,
                    'length' => ceil($size['height'] / 10) + 5,
                    'width' => ceil($size['width'] / 10) + 5,
                    'height' => 6,
                    'dead_weight' => round($component->unit_dead_weight, 3),
                    'cubic_weight' => round(($component->unit_dead_weight * ($size['width'] / 10 * 2) * ($size['height'] / 10 * 2)) / 100, 3),
                ],
            ];
        }
        $pieces = [];
        $pieces = ArrayHelper::merge($pieces, static::getPiece($size['width'] / 10, $productToOption, $component));
        if (isset($size['height'])) {
            $pieces = ArrayHelper::merge($pieces, static::getPiece($size['width'] / 10, $productToOption, $component));
            $pieces = ArrayHelper::merge($pieces, static::getPiece($size['height'] / 10, $productToOption, $component));
            $pieces = ArrayHelper::merge($pieces, static::getPiece($size['height'] / 10, $productToOption, $component));
            if (isset($size['depth'])) {
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['width'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['width'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['height'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['height'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['depth'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['depth'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['depth'] / 10, $productToOption, $component));
                $pieces = ArrayHelper::merge($pieces, static::getPiece($size['depth'] / 10, $productToOption, $component));
            }
        }
        return $pieces;
    }

    /**
     * @param float $length
     * @param ProductToOption $productToOption
     * @param Component $component
     * @return array
     */
    private static function getPiece($length, $productToOption, $component)
    {
        $pieces = [];
        if ($length > static::BOX_MAX_LENGTH) {
            $count = ceil($length / static::BOX_MAX_LENGTH);
            $length = $length / $count;
            for ($i = 0; $i < $count; $i++) {
                $pieces[] = [
                    'id' => 'P2O-' . $productToOption->id,
                    'code' => $component->code,
                    'length' => ceil($length),
                    'width' => 0,
                    'height' => 0,
                    'dead_weight' => round($component->unit_dead_weight * $length / 100, 3),
                    'cubic_weight' => round($component->unit_cubic_weight* $length / 100, 3),
                ];
            }
        } else {
            $pieces[] = [
                'id' => 'P2O-' . $productToOption->id,
                'code' => $component->code,
                'length' => ceil($length),
                'width' => 0,
                'height' => 0,
                'dead_weight' => round($component->unit_dead_weight * $length / 100, 3),
                'cubic_weight' => round($component->unit_cubic_weight * $length / 100, 3),
            ];
        }
        return $pieces;
    }

    /**
     * @param Component $component
     * @return bool
     */
    private static function allowFlatten($component)
    {
        if (in_array($component->code, ['ILS50'])) {
            return false;
        }
        return true;
    }

    /**
     * @param Component $component
     * @return bool
     */
    private static function allowComponentMerge($component)
    {
        if (in_array($component->code, ['ILS50', 'ILS80', 'ILS120', 'ILS140'])) {
            return false;
        }
        return true;
    }

    /**
     * @param Item $item
     * @return bool|ProductToOption
     */
    private static function getProductToOption($item)
    {
        $productToOption = $item->getProductToOption(Option::OPTION_REFRAME_EXTRUSION);
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

}