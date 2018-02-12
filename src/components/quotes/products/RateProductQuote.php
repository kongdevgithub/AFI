<?php

namespace app\components\quotes\products;

use app\models\Option;
use app\models\Product;
use Yii;

/**
 * RateProductQuote
 */
class RateProductQuote extends BaseProductQuote
{

    /**
     * @param Product $product
     * @return float
     */
    public function getQuoteFactor($product)
    {
        $rate = $product->getRate();
        if ($rate) {
            $factorMarkup = $this->getQuotePrice($product) * $product->job->quote_markup;
            if (!$factorMarkup) {
                return 0;
            }
            return ($rate['price'] * $rate['area'] * $rate['quantity']) / $factorMarkup;
        }
        return 1;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function forceQuoteFactor($product = null)
    {
        return true;
    }


}