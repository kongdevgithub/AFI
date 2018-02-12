<?php

namespace app\components\quotes\products;

use app\components\Helper;
use app\components\quotes\items\BaseItemQuote;
use app\models\Product;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * BaseProductQuote
 */
class BaseProductQuote extends Component
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
                $opts[__NAMESPACE__ . '\\' . str_replace('.php', '', $file)] = str_replace('ProductQuote.php', '', $file);
            }
        }
        $opts['app\components\quotes\products\BaseProductQuote'] = 'Product';
        asort($opts);
        return $opts;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getQuoteCost($product)
    {
        $quote = 0;
        foreach ($product->items as $item) {
            if ($item->quantity == 0) continue;
            $quote += $item->quote_total_cost;
        }
        return $quote;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getQuotePrice($product)
    {
        $quote = 0;
        foreach ($product->items as $item) {
            if ($item->quantity == 0) continue;
            $quote += $item->quote_factor_price;
        }
        return $quote;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getQuotePriceUnlocked($product)
    {
        $quote = 0;
        foreach ($product->items as $item) {
            if ($item->quantity == 0) continue;
            $quote += $item->quote_total_price_unlocked * $item->quote_factor;
        }
        return $quote;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getQuoteFactor($product)
    {
        return 1;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getQuoteWeight($product)
    {
        $weight = 0;
        foreach ($product->items as $item) {
            if ($item->quantity == 0) continue;
            $weight += $item->quote_weight;
        }
        return $weight;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getQuoteLabel($product = null)
    {
        return '<span title="' . Html::encode($this->getDescription($product)) . '" data-toggle="tooltip">' . Html::encode($this->getName($product)) . '</span>';
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getName($product = null)
    {
        return BaseProductQuote::opts()[static::className()];
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getDescription($product = null)
    {
        return BaseProductQuote::opts()[static::className()];
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function forceQuoteFactor($product = null)
    {
        if ($product->quote_factor == 0) {
            return true;
        }
        return false;
    }

    /**
     * @param Product $product
     * @param bool $verbose
     * @throws Exception
     */
    public function saveQuote($product, $verbose = false)
    {
        if ($product->quote_generated) {
            return;
        }

        // save Item quotes
        foreach ($product->items as $item) {
            /** @var BaseItemQuote $itemQuote */
            $itemQuote = new $item->quote_class;
            $itemQuote->saveQuote($item, $verbose);
        }

        // save cache keys
        $jobCacheKeyPrefix = $product->job->getCacheKeyPrefix();

        // save Product quote
        $productQuoteCost = $this->getQuoteCost($product);
        $productQuotePrice = $this->getQuotePrice($product);
        if ($this->forceQuoteFactor($product)) {
            if ($product->quote_retail_unit_price_import) {
                $importTotalPrice = ($product->quote_retail_unit_price_import * $product->quantity);
                $importQuotePrice = $product->job->quote_markup ? $importTotalPrice / $product->job->quote_markup : $importTotalPrice;
                $product->quote_factor = $productQuotePrice ? $importQuotePrice / $productQuotePrice : 0;
                $product->quote_retail_unit_price_import = null;
            } else {
                $product->quote_factor = $this->getQuoteFactor($product);
            }
        }
        $product->quote_weight = $this->getQuoteWeight($product);
        $product->quote_quantity = $product->quantity;
        $product->quote_unit_cost = $productQuoteCost / $product->quote_quantity;
        $product->quote_total_cost = $productQuoteCost;
        $product->quote_label = $this->getQuoteLabel($product);
        $product->quote_unit_price = $productQuotePrice / $product->quote_quantity;
        $product->quote_total_price = $productQuotePrice;
        $product->quote_total_price_unlocked = $this->getQuotePriceUnlocked($product);
        $product->quote_factor_price = $productQuotePrice * $product->quote_factor;

        $product->quote_generated = 1;
        if (!$product->save(false)) {
            throw new Exception('Cannot save product-' . $product->id . ': ' . Helper::getErrorString($product));
        }
        if ($verbose) {
            echo 'P';
        }

        // restore cache keys
        $product->job->setCacheKeyPrefix($jobCacheKeyPrefix);
    }
}