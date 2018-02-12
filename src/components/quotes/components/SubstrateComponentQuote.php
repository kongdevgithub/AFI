<?php

namespace app\components\quotes\components;

use app\components\BlockPacker;
use app\components\fields\ComponentField;
use app\models\Component;
use app\models\Item;
use app\models\Job;
use app\models\Option;
use app\models\Product;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * SubstrateComponentQuote
 */
class SubstrateComponentQuote extends BaseComponentQuote
{

    /**
     * @var float
     */
    protected $wastage = 1.1;
    /**
     * @var float
     */
    protected $offset = 300;

    /**
     * @param Component|null $component
     * @param Item|null $item
     * @return string
     */
    public function getDescription($component = null, $item = null)
    {
        if (!$component || !$item || !($item->quantity * $item->product->quantity)) {
            return Yii::t('app', 'Based on percent of area on product total lineal length including wastage.');
        }
        $unitQuantity = $item->quantity * $item->product->quantity;
        $area = $this->getArea($item);
        $totalArea = $this->getProductTotalArea($component, $item->product);
        $fraction = $totalArea && ($area * $unitQuantity) ? $totalArea / ($area * $unitQuantity) : 0;
        $percent = $fraction ? round((1 / $fraction) * 100, 2) : 0;
        $length = $this->getProductTotalHeight($component, $item) * $this->wastage / 1000;
        $wastage = ($this->wastage - 1) * 100;
        return Yii::t('app', 'Based on {percent}% of area on {length}m (product total lineal length including {wastage}% wastage).', [
            'percent' => $percent,
            'length' => $length,
            'wastage' => $wastage,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantityOld($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);
        $maxSize = max($size) / 1000;
        $minSize = min($size) / 1000;
        $offset = $this->offset / 1000;
        $substrateWidth = $this->getSubstrateWidth($item) / 1000;

        // both sides less than 3m, use the small side
        if ($maxSize <= $substrateWidth) {
            return $unitQuantity * ($minSize * $this->wastage + $offset);
        }

        // one side less than 3m, use the long side
        if ($minSize <= $substrateWidth) {
            return $unitQuantity * ($maxSize * $this->wastage + $offset);
        }

        // over 3m, needs multiple rows
        return $unitQuantity * (($maxSize + $offset) * $minSize / $substrateWidth * $this->wastage);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        if (!$component) {
            return 0;
        }
        $unitQuantity = $item->quantity * $item->product->quantity;
        if (!$unitQuantity) {
            return 0;
        }
        $area = $this->getArea($item);
        if (!$area) {
            return 0;
        }
        $totalArea = $this->getProductTotalArea($component, $item->product);
        $fraction = $totalArea ? $totalArea / ($area * $unitQuantity) : 0;
        $totalHeight = $this->getProductTotalHeight($component, $item);
        $quoteQuantity = $fraction ? $totalHeight * $this->wastage / $fraction / 1000 : 0;
        return round($quoteQuantity, 4);
    }

    /**
     * @param Component $component
     * @param Item $item
     * @return int
     */
    private function getProductTotalHeight($component, $item)
    {
        $cacheKey = 'SubstrateComponentQuote.getProductTotalHeight.' . $component->id;
        $height = $item->product->getCache($cacheKey);
        if ($height) {
            return $height;
        }

        $blocks = $this->getProductBlocks($component, $item->product);
        $blockPacker = new BlockPacker();
        $height = $blockPacker->fit($blocks, $this->getSubstrateWidth($item));
        $item->product->setCache($cacheKey, $height);
        return $height;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return array
     */
    private function getProductBlocks($component, $product)
    {
        $cacheKey = 'SubstrateComponentQuote.getProductBlocks.' . $component->id;
        $blocks = $product->getCache($cacheKey);
        if ($blocks) {
            return $blocks;
        }

        $blocks = [];
        foreach ($product->items as $_item) {
            foreach ($_item->productToOptions as $_productToOption) {
                if ($_item->split_id) {
                    continue;
                }
                if (!$_productToOption->option->field_class) {
                    continue;
                }
                /** @var ComponentField $field */
                $field = new $_productToOption->option->field_class;
                if (!$field instanceof ComponentField) {
                    continue;
                }
                $_component = $field->getComponent($_productToOption);
                if ($_component) {
                    if ($_component->id == $component->id && $_productToOption->quote_class == static::className()) {
                        $blocks = ArrayHelper::merge($blocks, $this->getBestItemBlocks($_item));
                    }
                }
            }
            foreach ($_item->productToComponents as $_productToComponent) {
                if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                    $blocks = ArrayHelper::merge($blocks, $this->getBestItemBlocks($_item));
                }
            }
        }
        $product->setCache($cacheKey, $blocks);
        return $blocks;
    }

    /**
     * @param Item $item
     * @return array
     */
    private function getBestItemBlocks($item)
    {
        $itemBlocks = [];
        $heights = [];
        foreach (['w', 'h'] as $offsetPosition) {
            foreach (['w', 'h'] as $labelPosition) {
                $key = $offsetPosition . '.' . $labelPosition;
                $itemBlocks[$key] = $this->getItemBlocks($item, $offsetPosition, $labelPosition);
                $blockPacker = new BlockPacker();
                $heights[$key] = $blockPacker->fit($itemBlocks[$key], $this->getSubstrateWidth($item));
            }
        }
        return $heights ? $itemBlocks[array_search(min($heights), $heights)] : [];
    }

    /**
     * @param Item $item
     * @param string $offsetPosition
     * @param string $labelPosition
     * @return array
     */
    private function getItemBlocks($item, $offsetPosition, $labelPosition)
    {
        $blocks = [];
        $block = $this->getItemBlock($item, $offsetPosition, $labelPosition);
        if ($block) {
            $quantity = $item->quantity * $item->product->quantity;
            foreach ($item->splits as $split) {
                $quantity += $split->quantity * $item->product->quantity;
            }
            for ($i = 0; $i < $quantity; $i++) {
                $blocks[] = (object)$block;
            }
        }
        //if (count($blocks) > 200) {
        //    $blocks = array_splice($blocks, 0, 200);
        //}
        return $blocks;
    }

    /**
     * @param Item $item
     * @param string $offsetPosition
     * @param string $labelPosition
     * @return array|bool
     */
    private function getItemBlock($item, $offsetPosition, $labelPosition)
    {
        $size = $this->getSize($item);
        if (!$size) {
            return false;
        }
        $min = min($size);
        $max = max($size);
        $substrateWidth = $this->getSubstrateWidth($item);

        // both sides less than 3m
        if ($max <= $substrateWidth) {
            if ($offsetPosition == 'w') {
                if ($labelPosition == 'w' && $max + $this->offset <= $substrateWidth) {
                    return ['w' => $max + $this->offset, 'h' => $min];
                }
                return ['w' => $max, 'h' => $min + $this->offset];
            }
        }

        // at least one side less than 3m
        if ($min <= $substrateWidth) {
            if ($labelPosition == 'w' && $min + $this->offset <= $substrateWidth) {
                return ['w' => $min + $this->offset, 'h' => $max];
            }
            return ['w' => $min, 'h' => $max + $this->offset];
        }

        // over 3m, needs multiple rows
        if ($offsetPosition == 'w') {
            return ['w' => $substrateWidth, 'h' => ($max + $this->offset) * ceil($min / $substrateWidth)];
        }
        return ['w' => $substrateWidth, 'h' => ($min + $this->offset) * ceil($max / $substrateWidth)];
    }

    /**
     * @param Item $item
     * @return int
     */
    protected function getSubstrateWidth($item)
    {
        $cacheKey = 'SubstrateWidth';
        $width = $item->product->getCache($cacheKey);
        if ($width) {
            return $width;
        }
        $substrateOption = $this->getProductToOption($item, Option::OPTION_SUBSTRATE);
        if ($substrateOption) {
            $substrateComponent = Component::findOne($substrateOption->getValueDecoded());
            if ($substrateComponent) {
                $config = $substrateComponent->getConfigDecoded();
                if (!empty($config['width'])) {
                    $item->product->setCache($cacheKey, $config['width']);
                    return $config['width'];
                }
            }
        }
        $width = 3000;
        $item->product->setCache($cacheKey, $width);
        return $width;
    }

}