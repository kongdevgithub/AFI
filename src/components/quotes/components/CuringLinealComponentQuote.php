<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Item;
use app\models\Option;
use Yii;

/**
 * CuringLinealComponentQuote
 */
class CuringLinealComponentQuote extends SubstrateComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the lineal length with a max width of 3m before multiple rows are needed.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $printerOption = $this->getProductToOption($item, Option::OPTION_PRINTER);
        $printerComponent = Component::findOne($printerOption->getValueDecoded());
        if (!$printerComponent) {
            return 0;
        }
        $config = $printerComponent->getConfigDecoded();
        if (!isset($config['print_method']) || !in_array($config['print_method'], ['transfer', 'cure'])) {
            return 0;
        }
        return parent::getQuoteQuantity($component, $item, $options);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantityOld($component, $item, $options = [])
    {
        $printerOption = $this->getProductToOption($item, Option::OPTION_PRINTER);
        $printerComponent = Component::findOne($printerOption->getValueDecoded());
        if (!$printerComponent) {
            return 0;
        }
        $config = $printerComponent->getConfigDecoded();
        if (!isset($config['print_method']) || !in_array($config['print_method'], ['transfer', 'cure'])) {
            return 0;
        }

        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);
        $maxSize = max($size) / 1000;
        $minSize = min($size) / 1000;
        $substrateWidth = $this->getSubstrateWidth($item) / 1000;

        // both sides less than 3m, use the small side
        if ($maxSize <= $substrateWidth) {
            return $unitQuantity * $minSize;
        }

        // one side less than 3m, use the long side
        if ($minSize <= $substrateWidth) {
            return $unitQuantity * $maxSize;
        }

        // over 3m, needs multiple rows
        return $unitQuantity * $maxSize * $minSize / $substrateWidth;
    }

}