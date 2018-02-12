<?php

namespace app\components\quotes\jobs;

use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\products\BaseProductQuote;
use app\models\Job;
use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * CostJobQuote
 */
class CostJobQuote extends BaseJobQuote
{

    /**
     * @inheritdoc
     */
    public function getDescription($job = null)
    {
        if ($job) {
            $price = $this->getQuotePrice($job);
            return Yii::t('app', 'Discount the entire margin {margin}.', [
                'margin' => round($price - $this->getQuoteCost($job), 2),
            ]);
        }
        return Yii::t('app', 'Discount the entire margin.');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteFactor($job)
    {
        $price = $this->getQuotePrice($job);
        $cost = $this->getQuoteCost($job);
        $newPrice = $price - ($price - $cost);
        return $price ? round($newPrice / $price, 4) : 0;
    }

}