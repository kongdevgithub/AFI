<?php

namespace app\components\fields;

use app\models\Component;
use app\models\ProductToOption;
use app\widgets\JavaScript;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * EmPrintField
 */
class EmPrintField extends ComponentField
{
    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        JavaScript::begin();
        $prefix = '.field-ProductToOptions_' . $key . '_';
        ?>
        <script>
            // show/hide fields
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded', function () {
                var $parent = $(this).closest('.table-cell');
                if ($(this).is(':checked')) {
                    $parent.find('<?= $prefix ?>quantity').show().find(':input').prop('disabled', false);
                } else {
                    $parent.find('<?= $prefix ?>quantity').hide().find(':input').prop('disabled', true);
                }
            });
            $('#ProductToOptions_<?= $key ?>_valueDecoded').change();
        </script>
        <?php
        JavaScript::end();

        $fields = [];
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkbox([
            'id' => "ProductToOptions_{$key}_valueDecoded",
            'name' => "ProductToOptions[$key][valueDecoded]",
            'label' => $productToOption->option->name,
        ])->label(false);
        $fields[] = $form->field($productToOption, 'quantity', [
            'options' => [
                'class' => 'form-group',
                'style' => $productToOption->valueDecoded ? '' : 'display:none',
            ],
        ])->textInput([
            'id' => "ProductToOptions_{$key}_quantity",
            'name' => "ProductToOptions[$key][quantity]",
        ])->label($productToOption->option->name . ' ' . Yii::t('app', 'Cost'));

        return implode(' ', $fields);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if ($component) {
            return Yii::t('app', 'OEM Print');
            //return $component->code; // . ' x' . ($productToOption->quote_quantity * 1);
        }
        return '';
    }

    /**
     * @param ProductToOption $productToOption
     * @return Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component && $productToOption->valueDecoded) {
            $this->_component = Component::findOne(Component::COMPONENT_OEM);
        }
        return $this->_component;
    }

}