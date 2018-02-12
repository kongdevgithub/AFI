<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Option;
use Yii;

/**
 * LedEdgeComponentQuote
 */
class LedEdgeComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Edge LED used every 500mm on longest side.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $productToOption = $item->getProductToOption(Option::OPTION_ILLUMINATED_EDGE);
        if (!$productToOption) {
            return 0;
        }
        if ($productToOption->getValueDecoded() != 'auto') {
            return 0;
        }

        $unitQuantity = $item->quantity * $item->product->quantity;
        $size = $this->getSize($item);

        $maxSize = max($size);
        $minSize = min($size);
        $rows = 1;
        if ($minSize > 1500 || $minSize > 1000 && $this->getProductToComponent($item, Component::COMPONENT_ILS80)) {
            $rows = 2;
        }
        return $unitQuantity * ceil($maxSize / 500) * $rows;
    }

}