<?php

namespace app\components\fields\validators;

use app\models\ProductToOption;
use Yii;
use yii\validators\Validator;

/**
 * FinishingFieldValidator
 */
class FinishingFieldValidator extends Validator
{

    /**
     * @param ProductToOption $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        //if (empty($value['position'])) {
        //    $this->addError($model, $attribute, Yii::t('app', '{name} Position is required.', [
        //        'name' => $model->option->name
        //    ]));
        //}
        //if (empty($value['component'])) {
        //    $this->addError($model, $attribute, Yii::t('app', '{name} Component is required.', [
        //        'name' => $model->option->name
        //    ]));
        //}
    }
}