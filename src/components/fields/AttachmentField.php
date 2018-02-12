<?php

namespace app\components\fields;

use Yii;

/**
 * AttachmentField
 */
class AttachmentField extends TextField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        return $form->field($productToOption, 'valueDecoded')->fileInput([
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
        ])->label($productToOption->option->name);
    }
}