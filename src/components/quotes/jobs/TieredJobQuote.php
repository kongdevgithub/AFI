<?php

namespace app\components\quotes\jobs;

use app\components\Helper;
use app\components\quotes\products\RateProductQuote;
use app\models\Job;
use Yii;

/**
 * TieredJobQuote
 */
class TieredJobQuote extends BaseJobQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($job = null)
    {
        if ($job) {
            $price = $this->getQuotePrice($job);
            return Yii::t('app', 'Factor {factor} of margin {margin} based on price {price}.', [
                'factor' => round($this->getQuoteMarginDiscountFactor($price), 2),
                'margin' => round($price - $this->getQuoteCost($job), 2),
                'price' => round($this->getQuotePrice($job), 2),
            ]);
        }
        return Yii::t('app', 'Factor of margin based on price.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteFactor($job)
    {
        $cap = 0.8; // 80% of original job value
        $price = $this->getQuotePrice($job) - $this->getRatePrice($job) + $job->getProductDiscount();
        $cost = $this->getQuoteCost($job) - $this->getRatesCost($job);
        if ($price <= $cost) {
            return 1;
        }
        $margin = $price - $cost;
        $newMargin = $margin * $this->getQuoteMarginDiscountFactor($price);
        $newPrice = $price - $margin + $newMargin;
        if ($newPrice < $price * $cap) {
            $newPrice = $price * $cap;
        }
        return $price ? round($newPrice / $price, 4) : 1;
    }

    /**
     * Returns a gradient between fixed discount factors
     *
     * @param $price
     * @return int|mixed
     */
    public function getQuoteMarginDiscountFactor($price)
    {
        $priceDiscountFactors = Yii::$app->settings->get('price_discount_factors', 'tiered_job_quote');
        return Helper::getAmountBetweenScale($price, $priceDiscountFactors);
    }

    /**
     * @param Job $job
     * @return float
     */
    public function getRatePrice($job)
    {
        $quote = 0;
        foreach ($job->products as $product) {
            if ($product->quote_class != RateProductQuote::className()) continue;
            $quote += $product->quote_factor_price - $product->quote_discount_price;
        }
        return $quote;
    }


    /**
     * @param Job $job
     * @return float
     */
    public function getRatesCost($job)
    {
        $quote = 0;
        foreach ($job->products as $product) {
            if ($product->quote_class != RateProductQuote::className()) continue;
            $quote += $product->quote_total_cost;
        }
        return $quote;
    }
}