<?php

namespace app\components\fields\validators;

use app\models\ProductToOption;
use Yii;
use yii\validators\Validator;

/**
 * ArtworkFieldValidator
 */
class ArtworkFieldValidator extends Validator
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
        //if ($value['quantity'] < 0) {
        //    $this->addError($model, $attribute, Yii::t('app', '{name} Quantity cannot be less than zero.', [
        //        'name' => $model->option->name
        //    ]));
        //}
        if (empty($value['component'])) {
            $this->addError($model, $attribute, Yii::t('app', '{name} Component is required.', [
                'name' => $model->option->name
            ]));
        }
    }
}