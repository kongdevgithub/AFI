<?php

namespace app\components\quotes\components;

use app\components\fields\ComponentField;
use app\components\Helper;
use app\models\Component;
use app\models\Item;
use app\models\Job;
use app\models\Option;
use app\models\Product;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use app\models\Size;
use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * BaseComponentQuote
 */
class BaseComponentQuote extends \yii\base\Component
{

    /**
     * @return array
     */
    public static function opts()
    {
        static $opts;
        if ($opts === null) {
            $opts = [];
            foreach (FileHelper::findFiles(__DIR__, ['recursive' => false]) as $file) {
                $file = basename($file);
                $opts[__NAMESPACE__ . '\\' . str_replace('.php', '', $file)] = str_replace('ComponentQuote.php', '', $file);
            }
        }
        $opts['app\components\quotes\components\BaseComponentQuote'] = 'Component';
        asort($opts);
        return $opts;
    }

    /**
     * @param Component $component
     * @return string
     */
    public function getName($component)
    {
        return $component->name;
    }

    /**
     * @param Component $component
     * @param Item $item
     * @param float|int $quantity
     * @param array $options
     * @return float
     */
    public function getQuoteTotalCost($component, $item, $quantity = 1, $options = [])
    {
        $quoteQuantity = $this->getQuoteQuantity($component, $item, $options);
        $quoteTotalCost = ($this->getUnitQuote($component) * $quantity * $quoteQuantity) + $this->getMakeReadyQuote($component, $item);
        $quoteMinimumCost = $this->getQuoteMinimumCost($component, $item);
        return max($quoteMinimumCost, $quoteTotalCost);
    }

    /**
     * @param Component $component
     * @param Item $item
     * @return float
     */
    public function getQuoteMinimumCost($component, $item)
    {
        $items = $this->getProductTotalItems($component, $item->product);
        if (!$items) {
            return 0;
        }
        return $component->minimum_cost / $items;
        //return $component->make_ready_cost / $this->getTotalItems($component, $job);
    }

    /**
     * @param Component $component
     * @param Item $item
     * @param float|int $quantity
     * @param null $quantityFactor
     * @param array $options
     * @return float
     */
    public function getQuoteTotalPrice($component, $item, $quantity = 1, $quantityFactor = null, $options = [])
    {
        $quoteFactor = $this->getQuoteFactor($component, $item, $quantityFactor);
        $quoteTotalCost = $this->getQuoteTotalCost($component, $item, $quantity, $options);
        return $quoteTotalCost * $quoteFactor;
    }

    /**
     * @param Component $component
     * @return float
     */
    public function getUnitQuote($component)
    {
        return $component->unit_cost;
    }

    /**
     * @param Component $component
     * @param Item $item
     * @param float|int $quantity
     * @return float
     */
    //public function getQuoteWeight($component, $item, $quantity = 1)
    //{
    //    return $component->unit_weight * $quantity * $item->quantity * $item->product->quantity;
    //}

    /**
     * @param Component $component
     * @param Item $item
     * @return float
     */
    public function getMakeReadyQuote($component, $item)
    {
        $items = $this->getProductTotalItems($component, $item->product);
        if (!$items) {
            return 0;
        }
        return $component->make_ready_cost / $items;
        //return $component->make_ready_cost / $this->getTotalItems($component, $job);
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return Item[]
     */
    public function getSharedJobComponentItems($component, $job)
    {
        //$cacheKey = 'BaseComponentQuote.getSharedJobComponentItems.' . $component->id;
        //$sharedComponentItems = $job->getCache($cacheKey);
        //if ($sharedComponentItems !== false) {
        //    return $sharedComponentItems;
        //}

        $sharedComponentItems = [];
        foreach ($job->products as $_product) {
            foreach ($_product->items as $_item) {
                foreach ($_item->productToOptions as $_productToOption) {
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
                            $sharedComponentItems[] = $_item;
                        }
                    }
                }
                foreach ($_item->productToComponents as $_productToComponent) {
                    if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                        $sharedComponentItems[] = $_item;
                    }
                }
            }
        }
        //$job->setCache($cacheKey, $sharedComponentItems);
        return $sharedComponentItems;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return Item[]
     */
    public function getSharedProductComponentItems($component, $product)
    {
        //$cacheKey = 'BaseComponentQuote.getSharedProductComponentItems.' . $component->id;
        //$sharedComponentItems = $product->getCache($cacheKey);
        //if ($sharedComponentItems !== false) {
        //    return $sharedComponentItems;
        //}

        $sharedComponentItems = [];
        foreach ($product->items as $_item) {
            if (!$_item->quantity) continue;
            foreach ($_item->productToOptions as $_productToOption) {
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
                        $sharedComponentItems[] = $_item;
                    }
                }
            }
            foreach ($_item->productToComponents as $_productToComponent) {
                if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                    $sharedComponentItems[] = $_item;
                }
            }
        }
        //$job->setCache($cacheKey, $sharedComponentItems);
        return $sharedComponentItems;
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return float
     */
    public function getTotalItems($component, $job)
    {
        $cacheKey = 'BaseComponentQuote.getTotalItems.' . $component->id;
        $quantity = $job->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = count($this->getSharedJobComponentItems($component, $job));
        $job->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return float
     */
    public function getProductTotalItems($component, $product)
    {
        $cacheKey = 'BaseComponentQuote.getProductTotalItems.' . $component->id;
        $quantity = $product->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = 0;
        $items = $this->getSharedProductComponentItems($component, $product);
        foreach ($items as $item) {
            if (!$item->quantity) continue;
            $quantity++;
        }
        $product->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return float
     */
    public function getTotalUnits($component, $job)
    {
        $cacheKey = 'BaseComponentQuote.getTotalUnits.' . $component->id;
        $quantity = $job->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = 0;
        foreach ($this->getSharedJobComponentItems($component, $job) as $item) {
            $quantity += $item->quantity * $item->product->quantity;
        }
        $job->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return float
     */
    public function getProductTotalUnits($component, $product)
    {
        $cacheKey = 'BaseComponentQuote.getProductTotalUnits.' . $component->id;
        $quantity = $product->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = 0;
        foreach ($this->getSharedProductComponentItems($component, $product) as $item) {
            $quantity += $item->quantity * $item->product->quantity;
        }
        $product->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return float
     */
    public function getTotalQuantity($component, $job)
    {
        $cacheKey = 'BaseComponentQuote.getTotalQuantity.' . $component->id;
        $quantity = $job->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = 0;
        foreach ($job->products as $_product) {
            foreach ($_product->items as $_item) {
                foreach ($_item->productToOptions as $_productToOption) {
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
                            $quantity += $field->getQuoteQuantity($_productToOption);
                        }
                    }
                }
                foreach ($_item->productToComponents as $_productToComponent) {
                    if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                        $quantity += $this->getQuoteQuantity($component, $_item);
                    }
                }
            }
        }
        $job->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return float
     */
    public function getProductTotalQuantity($component, $product)
    {
        $cacheKey = 'BaseComponentQuote.getProductTotalQuantity.' . $component->id;
        $quantity = $product->getCache($cacheKey);
        if ($quantity !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $quantity;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $quantity = 0;
        foreach ($product->items as $_item) {
            foreach ($_item->productToOptions as $_productToOption) {
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
                        $quantity += $field->getQuoteQuantity($_productToOption);
                    }
                }
            }
            foreach ($_item->productToComponents as $_productToComponent) {
                if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                    $quantity += $this->getQuoteQuantity($component, $_item);
                }
            }
        }
        $product->setCache($cacheKey, $quantity);
        return $quantity;
    }

    /**
     * @param Component|null $component
     * @param Item|null $item
     * @return string
     */
    public function getQuoteLabel($component = null, $item = null)
    {
        $title = Html::encode($this->getDescription($component, $item));
        $class = BaseComponentQuote::opts()[static::className()];
        return '<span title="' . $title . '" data-toggle="tooltip">' . $class . '</span>';
    }

    /**
     * @param Component|null $component
     * @param Item|null $item
     * @return string
     */
    public function getDescription($component = null, $item = null)
    {
        return BaseComponentQuote::opts()[static::className()];
    }

    /**
     * @param Component $component
     * @param Item $item
     * @param array $options
     * @return float
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        return $item->quantity * $item->product->quantity;
    }

    /**
     * @param Component $component
     * @param Item $item
     * @param null|string $quoteQuantityFactor
     * @return float
     */
    public function getQuoteFactor($component, $item, $quoteQuantityFactor = null)
    {
        $quantity = $this->getProductTotalQuantity($component, $item->product);
        if (!$quoteQuantityFactor) {
            $quoteQuantityFactor = $component->quantity_factor;
        }
        return Helper::getAmountBetweenScale($quantity, $quoteQuantityFactor);
    }

    /**
     * @param Item $item
     * @return array|bool
     */
    protected function getSize($item)
    {
        $cacheKey = 'BaseComponentQuote.getSize.' . $item->id;
        $size = $item->product->getCache($cacheKey);
        if ($size) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $size;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $size = $item->getSize();
        $item->product->setCache($cacheKey, $size);
        return $size;
    }

    /**
     * @param Item $item
     * @return float|int
     */
    protected function getArea($item)
    {
        $size = $this->getSize($item);
        if (!$size || empty($size['width']) || empty($size['height'])) {
            return 0;
        }
        return $size['width'] * $size['height'];
    }

    /**
     * @param Item $item
     * @return float|bool
     */
    protected function getPerimeter($item)
    {
        $size = $this->getSize($item);
        if (!$size) {
            return false;
        }
        if (!empty($size['depth'])) {
            return ($size['width'] + $size['height'] + $size['depth']) * 4;
        }
        if (!empty($size['height'])) {
            return ($size['width'] + $size['height']) * 2;
        }
        if (!empty($size['width'])) {
            return $size['width'];
        }
        return 0;
    }

    /**
     * @param Item $item
     * @return float|bool
     */
    protected function getWidth($item)
    {
        $size = $this->getSize($item);
        return $size ? $size['width'] : false;
    }

    /**
     * @param Item $item
     * @return float|bool
     */
    protected function getHeight($item)
    {
        $size = $this->getSize($item);
        return $size ? $size['height'] : false;
    }

    /**
     * @param Item $item
     * @return float|bool
     */
    protected function getDepth($item)
    {
        $size = $this->getSize($item);
        return $size ? $size['depth'] : false;
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return float
     */
    protected function getTotalArea($component, $job)
    {
        $cacheKey = 'BaseComponentQuote.getTotalArea.' . $component->id;
        $area = $job->getCache($cacheKey);
        if ($area !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $area;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $area = 0;
        foreach ($job->products as $_product) {
            foreach ($_product->items as $_item) {
                foreach ($_item->productToOptions as $_productToOption) {
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
                            $area += $this->getArea($_item) * $_item->quantity * $_product->quantity;
                        }
                    }
                }
                foreach ($_item->productToComponents as $_productToComponent) {
                    if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                        $area += $this->getArea($_item) * $_item->quantity * $_product->quantity;
                    }
                }
            }
        }
        $job->setCache($cacheKey, $area);
        return $area;
    }

    /**
     * @param Component $component
     * @param Product $product
     * @return float
     */
    protected function getProductTotalArea($component, $product)
    {
        $cacheKey = 'BaseComponentQuote.getProductTotalArea.' . $component->id;
        $area = $product->getCache($cacheKey);
        if ($area !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $area;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $area = 0;
        foreach ($product->items as $_item) {
            foreach ($_item->productToOptions as $_productToOption) {
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
                        $area += $this->getArea($_item) * $_item->quantity * $product->quantity;
                    }
                }
            }
            foreach ($_item->productToComponents as $_productToComponent) {
                if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                    $area += $this->getArea($_item) * $_item->quantity * $product->quantity;
                }
            }
        }
        $product->setCache($cacheKey, $area);
        return $area;
    }

    /**
     * @param Component $component
     * @param Job $job
     * @return float
     */
    protected function getTotalPerimeter($component, $job)
    {
        $cacheKey = 'BaseComponentQuote.getTotalPerimeter.' . $component->id;
        $perimeter = $job->getCache($cacheKey);
        if ($perimeter !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            return $perimeter;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        $perimeter = 0;
        foreach ($job->products as $_product) {
            foreach ($_product->items as $_item) {
                foreach ($_item->productToOptions as $_productToOption) {
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
                            $perimeter += $this->getPerimeter($_item) * $_item->quantity * $_product->quantity;
                        }
                    }
                }
                foreach ($_item->productToComponents as $_productToComponent) {
                    if ($_productToComponent->component_id == $component->id && $_productToComponent->quote_class == static::className()) {
                        $perimeter += $this->getPerimeter($_item) * $_item->quantity * $_product->quantity;
                    }
                }
            }
        }
        $job->setCache($cacheKey, $perimeter);
        return $perimeter;
    }

    /**
     * @param Item $item
     * @param int $option_id
     * @return ProductToOption|bool
     */
    protected function getProductToOption($item, $option_id)
    {
        $cacheKey = 'BaseComponentQuote.getProductToOption.' . $item->id . '.' . $option_id;
        $productToOptionId = $item->product->getCache($cacheKey);
        if ($productToOptionId !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            if ($productToOptionId) {
                return ProductToOption::findOne($productToOptionId);
            }
            return false;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        foreach ($item->productToOptions as $productToOption) {
            if ($productToOption->option_id == $option_id) {
                $item->product->setCache($cacheKey, $productToOption->id);
                return $productToOption;
            }
        }
        foreach ($item->product->productToOptions as $productToOption) {
            if ($productToOption->item_id) {
                continue;
            }
            if ($productToOption->option_id == $option_id) {
                $item->product->setCache($cacheKey, $productToOption->id);
                return $productToOption;
            }
        }
        $item->product->setCache($cacheKey, null);
        return false;
    }

    /**
     * @param Item $item
     * @param int $component_id
     * @return ProductToComponent|bool
     */
    protected function getProductToComponent($item, $component_id)
    {
        $cacheKey = 'BaseComponentQuote.getProductToComponent.' . $item->id . '.' . $component_id;
        $productToComponentId = $item->product->getCache($cacheKey);
        if ($productToComponentId !== false) {
            Yii::trace('CACHE HIT: ' . $cacheKey);
            if ($productToComponentId) {
                return ProductToComponent::findOne($productToComponentId);
            }
            return false;
        }
        Yii::trace('CACHE MISS: ' . $cacheKey);
        foreach ($item->productToComponents as $productToComponent) {
            if ($productToComponent->component_id == $component_id) {
                $item->product->setCache($cacheKey, $productToComponent->id);
                return $productToComponent;
            }
        }
        foreach ($item->product->productToComponents as $productToComponent) {
            if ($productToComponent->item_id) {
                continue;
            }
            if ($productToComponent->component_id == $component_id) {
                $item->product->setCache($cacheKey, $productToComponent->id);
                return $productToComponent;
            }
        }
        $item->product->setCache($cacheKey, null);
        return false;
    }

    /**
     * @param ProductToComponent $productToComponent
     * @param bool $verbose
     * @throws Exception
     */
    public function saveQuote($productToComponent, $verbose = false)
    {
        if ($productToComponent->quote_generated) {
            return;
        }

        // save ProductToComponent quote
        $productToComponent->quote_label = $this->getQuoteLabel($productToComponent->component, $productToComponent->item);
        $productToComponent->quote_quantity = $this->getQuoteQuantity($productToComponent->component, $productToComponent->item) * $productToComponent->quantity;
        $productToComponent->quote_generated = 1;
        if ($productToComponent->quote_quantity > 0) {
            $productToComponent->quote_make_ready_cost = $this->getMakeReadyQuote($productToComponent->component, $productToComponent->item);
            $productToComponent->quote_minimum_cost = $this->getQuoteMinimumCost($productToComponent->component, $productToComponent->item);
            $productToComponent->quote_unit_cost = $this->getUnitQuote($productToComponent->component);
            $productToComponent->quote_total_cost = $this->getQuoteTotalCost($productToComponent->component, $productToComponent->item, $productToComponent->quantity);
            $productToComponent->quote_factor = $this->getQuoteFactor($productToComponent->component, $productToComponent->item, $productToComponent->quote_quantity_factor);
            $productToComponent->quote_total_price = $this->getQuoteTotalPrice($productToComponent->component, $productToComponent->item, $productToComponent->quantity, $productToComponent->quote_quantity_factor);
            $productToComponent->quote_weight = 0; //$this->getQuoteWeight($productToComponent->component, $productToComponent->item, $productToComponent->quantity) * $productToComponent->quote_quantity;
        } else {
            $productToComponent->quote_make_ready_cost = 0;
            $productToComponent->quote_minimum_cost = 0;
            $productToComponent->quote_unit_cost = 0;
            $productToComponent->quote_total_cost = 0;
            $productToComponent->quote_factor = 0;
            $productToComponent->quote_total_price = 0;
            $productToComponent->quote_weight = 0;
        }
        if (!$productToComponent->save(false)) {
            throw new Exception('Cannot save productToComponent-' . $productToComponent->id . ': ' . Helper::getErrorString($productToComponent));
        }
        if ($verbose) {
            echo 'C';
        }
    }
}
