<?php

namespace app\components\fields;

use app\components\quotes\components\BaseComponentQuote;
use app\models\ProductToOption;
use app\models\ProductTypeToOption;
use yii\base\Component;
use kartik\form\ActiveForm;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * Field
 */
abstract class BaseField extends Component
{

    /**
     * @return array
     */
    public static function opts()
    {
        static $opts;
        if ($opts === null) {
            $opts = [];
            foreach (FileHelper::findFiles(__DIR__, ['recursive' => false]) as $file) {
                $file = basename($file);
                $opts[__NAMESPACE__ . '\\' . str_replace('.php', '', $file)] = str_replace('Field.php', '', $file);
            }
        }
        return $opts;
    }

    /**
     * @return string
     */
    public static function name()
    {
        $className = static::className();
        $className = substr(static::className(), strrpos($className, '\\') + 1);
        return str_replace('Field', '', $className);
    }

    /**
     * @param ProductToOption $productToOption
     * @return array
     */
    public function rulesProduct($productToOption)
    {
        $rules = [];
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->required) {
            $rules['valueDecoded_required'] = [['valueDecoded'], 'required'];
        }
        return $rules;
    }

    /**
     * @param ProductToOption $productToOption
     * @param ActiveForm $form
     * @param string $key
     * @return string
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        return '';
    }

    /**
     * @param ProductToOption $productToOption
     * @return array|bool
     */
    public function attributeProduct($productToOption)
    {
        $value = $this->attributeValueProduct($productToOption);
        if (!$value) {
            return false;
        }
        return [
            'label' => $this->attributeLabelProduct($productToOption),
            'value' => $value,
            'format' => 'raw',
        ];
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function attributeValueProduct($productToOption)
    {
        return $productToOption->getValueDecoded();
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function attributeLabelProduct($productToOption)
    {
        return $productToOption->option->name;
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function nameProduct($productToOption)
    {
        return $this->attributeValueProduct($productToOption);
    }

    /**
     * @param ProductTypeToOption $productTypeToOption
     * @param ActiveForm $form
     * @return string
     */
    public function fieldProductType($productTypeToOption, $form)
    {
        return '';
    }

    /**
     * @param ProductTypeToOption $productTypeToOption
     * @return string
     */
    public function valuesProductType($productTypeToOption)
    {
        return implode(' ', [
            '<strong>' . ($productTypeToOption->describes_item ? '*' : '') . $productTypeToOption->option->name . '</strong> ',
            '<span class="label label-primary">' . ($productTypeToOption->required ? '*' : '') . $this->name() . '</span>',
            $this->allowedValuesHtml($productTypeToOption),
        ]);
    }

    /**
     * @param ProductTypeToOption $productTypeToOption
     * @return array
     */
    public function allowedValues($productTypeToOption)
    {
        return [];
    }

    /**
     * @param ProductTypeToOption $productTypeToOption
     * @return string
     */
    public function allowedValuesHtml($productTypeToOption)
    {
        $values = [];
        foreach ($this->allowedValues($productTypeToOption) as $value) {
            $values[] = '<span class="label label-default">' . $value . '</span>';
        }
        return implode(' ', $values);
    }

}