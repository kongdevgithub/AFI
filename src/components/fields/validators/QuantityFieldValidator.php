<?php

namespace app\components\fields\validators;

use app\models\ProductToOption;
use Yii;
use yii\validators\Validator;

/**
 * QuantityFieldValidator
 */
class QuantityFieldValidator extends Validator
{

    /**
     * @param ProductToOption $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        //if ($value['quantity'] === '' || $value['quantity'] === null) {
        //    $this->addError($model, $attribute, Yii::t('app', '{name} Quantity is required.', [
        //        'name' => $model->option->name
        //    ]));
        //}
        if ($model->productTypeToOption && $model->productTypeToOption->required) {
            if (empty($value['component'])) {
                $this->addError($model, $attribute, Yii::t('app', '{name} Component is required.', [
                    'name' => $model->option->name
                ]));
            }
            if ($model->quantity <= 0) {
                $this->addError($model, $attribute, Yii::t('app', '{name} Quantity must be greater than 0.', [
                    'name' => $model->option->name
                ]));
            }
        } else {
            if ($model->quantity < 0) {
                $this->addError($model, $attribute, Yii::t('app', '{name} Quantity cannot be less than 0.', [
                    'name' => $model->option->name
                ]));
            }
        }
    }
}