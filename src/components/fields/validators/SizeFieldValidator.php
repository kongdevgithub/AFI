<?php

namespace app\components\fields\validators;

use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\validators\Validator;

/**
 * SizeFieldValidator
 */
class SizeFieldValidator extends Validator
{

    public $maxShortSide = null;

    /**
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        // validate value
        if (empty($value['value'])) {
            $this->addError($model, $attribute, Yii::t('app', 'Size is required.'));
        }

        // validate dimensions
        foreach (['width', 'height', 'depth'] as $dimension) {
            if (isset($value[$dimension]) && $value[$dimension] === '') {
                $this->addError($model, $attribute, Yii::t('app', '{dimension} is required.', ['dimension' => Inflector::humanize($dimension)]));
            }
        }

        // validate max size
        unset($value['value']);
        if ($this->maxShortSide !== null && min($value) > $this->maxShortSide) {
            $this->addError($model, $attribute, Yii::t('app', 'The maximum size of the shortest side is {maxShortSide}.', [
                'maxShortSide' => $this->maxShortSide,
            ]));
        }
    }
}