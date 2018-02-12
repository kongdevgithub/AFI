<?php

namespace app\components\quotes\items;

use app\components\fields\ComponentField;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Item;
use app\models\Option;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * BaseItemQuote
 */
class BaseItemQuote extends Component
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
                $class = __NAMESPACE__ . '\\' . str_replace('.php', '', basename($file));
                /** @var BaseItemQuote $itemQuote */
                $itemQuote = new $class;
                $opts[$class] = $itemQuote->getDescription();
            }
        }
        $opts[__NAMESPACE__ . '\\BaseItemQuote'] = 'Item';
        asort($opts);
        return $opts;
    }

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteCost($item)
    {
        if ($item->isEmPrint()) {
            return $this->getQuoteCostEmPrint($item);
        }

        $quote = 0;
        // options
        foreach ($item->productToOptions as $productToOption) {
            $quote += $productToOption->quote_total_cost;
        }
        // components
        foreach ($item->productToComponents as $productToComponent) {
            $quote += $productToComponent->quote_total_cost;
        }
        return $quote;
    }

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteCostEmPrint($item)
    {
        $quote = 0;
        $productToOption = $item->getProductToOption(Option::OPTION_EM_PRINT);
        if ($productToOption && $productToOption->quote_total_cost) {
            // em print
            $quote += $productToOption->quote_total_cost;
        } else {
            // options
            foreach ($item->productToOptions as $productToOption) {
                $quote += $productToOption->quote_total_cost;
            }
            // components
            foreach ($item->productToComponents as $productToComponent) {
                $quote += $productToComponent->quote_total_cost;
            }
        }
        return $quote;
    }

    /**
     * @param Item $item
     * @return float
     */
    public function getQuotePrice($item)
    {
        $quote = 0;
        // options
        foreach ($item->productToOptions as $productToOption) {
            $quote += $productToOption->quote_total_price;
        }
        // components
        foreach ($item->productToComponents as $productToComponent) {
            $quote += $productToComponent->quote_total_price;
        }
        return $quote;
    }

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        return 1;
    }

    /**
     * @param Item $item
     * @return float
     */
    //public function getQuoteWeight($item)
    //{
    //    $weight = 0;
    //    // options
    //    foreach ($item->productToOptions as $productToOption) {
    //        $weight += $productToOption->quote_weight;
    //    }
    //    // components
    //    foreach ($item->productToComponents as $productToComponent) {
    //        $weight += $productToComponent->quote_weight;
    //    }
    //    return $weight;
    //}

    /**
     * @param Item $item
     * @return string
     */
    public function getQuoteLabel($item = null)
    {
        $preserve = '';
        if ($this->preserveUnitPrices($item)) {
            $preserve = '*';
        }
        return '<span title="' . Html::encode($this->getDescription($item)) . ($preserve ? ' ***' . Yii::t('app', 'preserved unit price from quote') . '***' : '') . '" data-toggle="tooltip">' . Html::encode($this->getName($item)) . $preserve . '</span>';
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getName($item = null)
    {
        return substr(basename(str_replace('\\', '/', static::className())), 0, -9);
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getDescription($item = null)
    {
        return BaseItemQuote::opts()[static::className()];
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function preserveUnitPrices($item)
    {
        return $item->product->preserve_unit_prices;// || $item->product->job->status != 'job/draft';
    }

    /**
     * @param Item $item
     * @param bool $verbose
     * @throws Exception
     */
    public function saveQuote($item, $verbose = false)
    {
        if ($item->quote_generated) {
            return;
        }

        // save ProductToOption quotes
        foreach ($item->productToOptions as $productToOption) {
            if (!$productToOption->option->field_class) {
                continue;
            }
            /** @var ComponentField $field */
            $field = new $productToOption->option->field_class;
            if (!$field instanceof ComponentField) {
                continue;
            }
            $field->saveQuote($productToOption, $verbose);
        }

        // save ProductToComponent quotes
        foreach ($item->productToComponents as $productToComponent) {
            /** @var BaseComponentQuote $componentQuote */
            $componentQuote = new $productToComponent->quote_class;
            $componentQuote->saveQuote($productToComponent, $verbose);
        }

        // save cache keys
        $jobCacheKeyPrefix = $item->product->job->getCacheKeyPrefix();
        $productCacheKeyPrefix = $item->product->getCacheKeyPrefix();

        // set the price to the quoted price
        if ($this->preserveUnitPrices($item)) {
            $itemQuotePrice = $item->quote_unit_price * $item->quantity * $item->product->quantity;
            $itemQuotePriceUnlocked = $this->getQuotePrice($item);
            $itemQuoteFactor = $item->quote_factor;
        } else {
            $itemQuotePrice = $this->getQuotePrice($item);
            $itemQuotePriceUnlocked = $itemQuotePrice;
            $itemQuoteFactor = $this->getQuoteFactor($item);
        }

        // save Item quote
        $itemQuoteCost = $this->getQuoteCost($item);
        $item->quote_label = $this->getQuoteLabel($item);
        $item->quote_factor = $itemQuoteFactor;
        $item->quote_weight = 0; //$this->getQuoteWeight($item);
        $item->quote_quantity = $item->quantity * $item->product->quantity;
        $item->quote_unit_cost = $item->quote_quantity ? $itemQuoteCost / $item->quote_quantity : 0;
        $item->quote_total_cost = $itemQuoteCost;
        $item->quote_unit_price = $item->quote_quantity ? $itemQuotePrice / $item->quote_quantity : 0;
        $item->quote_total_price = $itemQuotePrice;
        $item->quote_total_price_unlocked = $itemQuotePriceUnlocked;
        $item->quote_factor_price = $itemQuotePrice * $item->quote_factor;
        $item->quote_generated = 1;
        if (!$item->save(false)) {
            throw new Exception('Cannot save job-' . $item->id . ': ' . Helper::getErrorString($item));
        }
        if ($verbose) {
            echo 'I';
        }

        // restore cache keys
        $item->product->job->setCacheKeyPrefix($jobCacheKeyPrefix);
        $item->product->setCacheKeyPrefix($productCacheKeyPrefix);

    }
}