<?php

namespace app\components\fields;

use Yii;
use yii\helpers\Html;

/**
 * CheckboxField
 */
class CheckboxField extends BaseField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        return $form->field($productToOption, 'valueDecoded')->checkbox([
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'label' => $productToOption->option->name,
        ])->label(false);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        return $productToOption->value ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
    }
}