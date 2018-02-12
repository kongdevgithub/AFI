<?php
namespace app\models\validator;

use app\models\Job;
use Yii;

/**
 * JobQuoteDiscountPriceValidator
 * @package app\models\validator
 */
class JobQuoteDiscountPriceValidator extends \yii\validators\NumberValidator
{

    /**
     * @param Job $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $this->tooBig = Yii::t('app', '{attribute} must be no greater than {max}.');
        if (Yii::$app->user->can('manager')) {
            $this->max = $model->quote_retail_price * 1;
        } else {
            $this->max = $model->quote_maximum_discount_price * 1;
        }
        parent::validateAttribute($model, $attribute);
    }


}