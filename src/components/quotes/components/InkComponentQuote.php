<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Option;
use Yii;

/**
 * InkComponentQuote
 */
class InkComponentQuote extends BaseComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Based on the area plus 20% wastage.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        if (!$this->hasPrintComponent($item)) {
            return 0;
        }

        $unitQuantity = $item->quantity * $item->product->quantity;
        if ($this->hasPrintComponent($item, Option::OPTION_PRINTER_BACK)) {
            $unitQuantity *= 2;
        }
        $wastage = 1.2; // %
        $size = $this->getSize($item);
        if (!$size) {
            return 0;
        }
        $maxSize = max($size) / 1000;
        $minSize = min($size) / 1000;
        return $unitQuantity * $maxSize * $minSize * $wastage;
    }

    /**
     * @param $item
     * @param int $option
     * @return bool
     */
    private function hasPrintComponent($item, $option = Option::OPTION_PRINTER)
    {
        $printerOption = $this->getProductToOption($item, $option);
        if (!$printerOption) {
            return false;
        }
        $component_id = $printerOption->getValueDecoded();
        if (!$component_id) {
            return false;
        }
        $printerComponent = Component::findOne($component_id);
        if (!$printerComponent || $printerComponent->id != Component::COMPONENT_PRINT) {
            return false;
        }
        return true;
    }

}