<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Item;
use app\models\Option;
use Yii;

/**
 * SewingJoinComponentQuote
 */
class SewingJoinComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getName($component)
    {
        return Yii::t('app', 'Join');
    }

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the substrate joins required.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $unitQuantity = $item->quantity * $item->product->quantity;
        if ($unitQuantity <= 0) {
            return 0;
        }
        $quantity = 0;
        $size = $this->getSize($item);
        $maxSize = max($size) / 1000;
        $minSize = min($size) / 1000;
        $substrateWidth = ($this->getSubstrateWidth($item) / 1000) + 0.001;
        $joins = floor($minSize / $substrateWidth);
        if ($joins) {
            $quantity += $joins * $maxSize;
        }
        return $unitQuantity * $quantity;
    }

    /**
     * @param Item $item
     * @return int
     */
    protected function getSubstrateWidth($item)
    {
        $cacheKey = 'SubstrateWidth.' . $item->id;
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