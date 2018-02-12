<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Option;
use Yii;

/**
 * CuringTransferComponentQuote
 */
class CuringTransferComponentQuote extends AreaComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'Area in m^2 where printer requires transfer curing.');
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
        if (!isset($config['print_method']) || !in_array($config['print_method'], ['transfer'])) {
            return 0;
        }
        return parent::getQuoteQuantity($component, $item, $options);
    }

}