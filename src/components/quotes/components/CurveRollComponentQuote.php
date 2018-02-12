<?php

namespace app\components\quotes\components;

use app\models\Component;
use app\models\Option;
use Yii;

/**
 * CurveRollComponentQuote
 */
class CurveRollComponentQuote extends AreaComponentQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($component = null, $item = null)
    {
        return Yii::t('app', 'One roll every 3m.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteQuantity($component, $item, $options = [])
    {
        $curveOption = $this->getProductToOption($item, Option::OPTION_CURVE);
        if (!$curveOption) {
            return 0;
        }
        $config = $curveOption->getValueDecoded();
        if (empty($config['type'])) {
            return 0;
        }
        if ($config['type'] == 'cylinder') {
            return ceil($config['length'] / 1000 / 3) * 2;
        }
        if ($config['type'] == 'circle') {
            return ceil($config['length'] / 1000 / 3);
        }
        return parent::getQuoteQuantity($component, $item, $options);
    }

}