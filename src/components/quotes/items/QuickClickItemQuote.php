<?php

namespace app\components\quotes\items;

use app\models\Component;
use app\models\Item;
use app\models\Option;
use Yii;

/**
 * QuickClickItemQuote
 */
class QuickClickItemQuote extends BaseItemQuote
{

    /**
     * @param Item $item
     * @return float
     */
    public function getQuoteFactor($item = null)
    {
        if (!$item) {
            return 1;
        }
        $cost = $this->getQuoteCost($item);
        if (!$cost) {
            return 1;
        }
        $price = $this->getPrice($item);
        if (!$price) {
            return 1;
        }
        return ($price / $item->product->job->quote_markup) / ($cost * 2);
    }

    /**
     * @inheritdoc
     */
    public function getDescription($item = null)
    {
        return Yii::t('app', 'Quick Click fixed prices.', [
            'factor' => $this->getQuoteFactor($item),
        ]);
    }

    /**
     * LARGE: (L-FL, L-HC, L-VC) - FRAME $400, SS SKIN $450, DS SKIN $560
     * EXTRA LARGE: (XL-FL, XL-HC, XL-VC) - FRAME $500, SS SKIN $600, DS SKIN $750
     *
     * @param Item $item
     * @return float
     * @return int
     */
    private function getPrice($item)
    {
        if (!$item) {
            return false;
        }
        $quantity = $item->quantity * $item->product->quantity;
        // LARGE FRAME
        if ($item->product_type_to_item_type_id == 350) {
            return 355 * $quantity;
        }
        // LARGE SS SKIN
        if ($item->product_type_to_item_type_id == 349) {
            return 495 * $quantity;
        }
        // LARGE DS SKIN
        if ($item->product_type_to_item_type_id == 378) {
            $productToOption = $item->getProductToOption(Option::OPTION_PRINTER);
            if (!$productToOption || !$productToOption->valueDecoded || $productToOption->valueDecoded == Component::COMPONENT_BLANK) {
                return 0.001;
            }
            return 110 * $quantity;
        }
        // EXTRA LARGE FRAME
        if ($item->product_type_to_item_type_id == 352) {
            return 400 * $quantity;
        }
        // EXTRA LARGE SS SKIN
        if ($item->product_type_to_item_type_id == 351) {
            return 700 * $quantity;
        }
        // EXTRA LARGE DS SKIN
        if ($item->product_type_to_item_type_id == 380) {
            $productToOption = $item->getProductToOption(Option::OPTION_PRINTER);
            if (!$productToOption || !$productToOption->valueDecoded || $productToOption->valueDecoded == Component::COMPONENT_BLANK) {
                return 0.001;
            }
            return 150 * $quantity;
        }
        return false;
    }
}