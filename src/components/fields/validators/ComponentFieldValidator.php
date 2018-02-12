<?php

namespace app\components\fields\validators;

use app\components\fields\ComponentField;
use app\models\ProductToOption;
use Yii;
use yii\validators\Validator;

/**
 * ComponentFieldValidator
 */
class ComponentFieldValidator extends Validator
{
    /**
     * @param ProductToOption $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        //$value = $model->$attribute;
//
//
//        /** @var ComponentField $componentField */
//        $componentField = new $productToOption->option->field_class;
//        if ($componentField instanceof ComponentField) {
//            $component = $componentField->getComponent($productToOption);
//            if (isset($component->component_config['restrict'])){
//                debug($component->component_config['restrict']); die;
//            }
//            die;
//        }
//
//
//        foreach ($model->item->productToOptions as $productToOption) {
//            /** @var ComponentField $componentField */
//            $componentField = new $productToOption->option->field_class;
//            if ($componentField instanceof ComponentField) {
//                $component = $componentField->getComponent($productToOption);
//                if (isset($component->component_config['restrict'])){
//                    debug($component->component_config['restrict']); die;
//                }
//                die;
//            }
//        }


    }
}