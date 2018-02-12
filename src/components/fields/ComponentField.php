<?php

namespace app\components\fields;

use app\components\fields\validators\ComponentFieldValidator;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ProductToOption;
use kartik\select2\Select2;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/**
 * ComponentField
 */
class ComponentField extends BaseField
{

    /**
     * @var Component
     */
    protected $_component;

    /**
     * @param ProductToOption $productToOption
     * @param ActiveForm $form
     * @param string $key
     * @return string
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $query = Component::find()->notDeleted();
        $fieldConfig = $productToOption->option->getFieldConfigDecoded();
        $default = isset($fieldConfig['default']) ? $fieldConfig['default'] : null;
        $ajax = null;

        if (isset($fieldConfig['condition']) || ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded())) {
            if (isset($fieldConfig['condition'])) {
                $query->andWhere($fieldConfig['condition']);
            }
            if ($productToOption->productTypeToOption) {
                $values = $productToOption->productTypeToOption->getValuesDecoded();
                if ($values) {
                    $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
                }
            }
        } else {
            $query->andWhere(['id' => $productToOption->valueDecoded]);
            $ajax = [
                'url' => Url::to(['component/json-list']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ];
        }
        $data = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'label');
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->required && count($data) == 1) {
            $default = key($data);
        }

        //if (Yii::$app->request->isAjax) {
        //    return $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
        //        'id' => "ProductToOptions_{$key}_valueDecoded",
        //        'name' => "ProductToOptions[$key][valueDecoded]",
        //        'prompt' => '',
        //        'value' => $productToOption->valueDecoded ? $productToOption->valueDecoded : $default,
        //    ])->label($productToOption->option->name);
        //}

        return $form->field($productToOption, 'valueDecoded')->widget(Select2::className(), [
            //'model' => $productToOption,
            //'attribute' => 'valueDecoded',
            'data' => isset($data) ? $data : [],
            'options' => [
                'id' => "ProductToOptions_{$key}_valueDecoded",
                'name' => "ProductToOptions[$key][valueDecoded]",
                'value' => $productToOption->valueDecoded ? $productToOption->valueDecoded : $default,
                'theme' => 'krajee',
                'placeholder' => '',
                'language' => 'en-US',
                'width' => '100%',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => $ajax ? 1 : 0,
                'ajax' => $ajax,
            ],
        ])->label($productToOption->option->name);
    }

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules['valueDecoded_component'] = [['valueDecoded'], ComponentFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        if ($productToOption->quote_quantity <= 0) {
            return '';
        }
        $component = $this->getComponent($productToOption);
        return $productToOption->option->name . ($component ? ': ' . $component->code : '');
        // . ($productToOption->quantity ? ' x' . ($productToOption->quantity * 1) : '');
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function attributeLabelProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if ($component) {
            return $component->code;
        }
        return $productToOption->option->name;
    }

    /**
     * @inheritdoc
     */
    public function fieldProductType($productTypeToOption, $form)
    {
        $query = Component::find()->notDeleted();
        $fieldConfig = $productTypeToOption->option->getFieldConfigDecoded();
        if (isset($fieldConfig['condition'])) {
            $query->andWhere($fieldConfig['condition']);
        }
        $query->orderBy(['name' => SORT_ASC]);
        $data = [];
        foreach ($query->all() as $row) {
            $data[$row->id] = $row->name . ' (' . $row->code . ')';
        }

        $fields = [];
        $fields[] = $form->field($productTypeToOption, 'valuesDecoded')->widget(Select2::className(), [
            'model' => $productTypeToOption,
            'attribute' => 'values',
            'data' => $data,
            'options' => [
                'multiple' => true,
                'theme' => 'krajee',
                'placeholder' => '',
                'language' => 'en-US',
                'width' => '100%',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        return implode(' ', $fields);
    }

    /**
     * @inheritdoc
     */
    public function allowedValues($productTypeToOption)
    {
        $allowedValues = [];
        $values = $productTypeToOption->getValuesDecoded();
        if ($values) {
            foreach ($values as $value) {
                $component = Component::findOne($value);
                if ($component) {
                    $allowedValues[] = $component->code ? $component->code : $component->name;
                }
            }
        }
        return $allowedValues;
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuoteTotalCost($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $quoteClass = $this->getQuoteClass($productToOption);
        $componentQuote = new $quoteClass;
        return $componentQuote->getQuoteTotalCost($component, $productToOption->item, $this->getQuantity($productToOption), $productToOption->getValueDecoded());
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuoteFactor($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $quoteClass = $this->getQuoteClass($productToOption);
        $componentQuote = new $quoteClass;
        return $componentQuote->getQuoteFactor($component, $productToOption->item, $productToOption->quote_quantity_factor);
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuoteMinimumCost($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $quoteClass = $this->getQuoteClass($productToOption);
        $componentQuote = new $quoteClass;
        return $componentQuote->getQuoteMinimumCost($component, $productToOption->item);
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuoteTotalPrice($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $quoteClass = $this->getQuoteClass($productToOption);
        $componentQuote = new $quoteClass;
        return $componentQuote->getQuoteTotalPrice($component, $productToOption->item, $this->getQuantity($productToOption), $productToOption->quote_quantity_factor, $productToOption->getValueDecoded());
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    //public function getQuoteWeight($productToOption)
    //{
    //    $component = $this->getComponent($productToOption);
    //    if (!$component) {
    //        return 0;
    //    }
    //    /** @var BaseComponentQuote $componentQuote */
    //    $quoteClass = $this->getQuoteClass($productToOption);
    //    $componentQuote = new $quoteClass;
    //    return $componentQuote->getQuoteWeight($component, $productToOption->item, $this->getQuantity($productToOption));
    //}

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuoteQuantity($productToOption)
    {
        $quoteClass = $this->getQuoteClass($productToOption);
        if (!$quoteClass) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $componentQuote = new $quoteClass;
        $quoteQuantity = $componentQuote->getQuoteQuantity($this->getComponent($productToOption), $productToOption->item, $productToOption->getValueDecoded());
        return $quoteQuantity * $this->getQuantity($productToOption);
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getUnitQuote($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        $quoteClass = $this->getQuoteClass($productToOption);
        if (!$quoteClass) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $componentQuote = new $quoteClass;
        return $componentQuote->getUnitQuote($component);
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getMakeReadyQuote($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return 0;
        }
        $quoteClass = $this->getQuoteClass($productToOption);
        if (!$quoteClass) {
            return 0;
        }
        /** @var BaseComponentQuote $componentQuote */
        $componentQuote = new $quoteClass;
        return $componentQuote->getMakeReadyQuote($component, $productToOption->item);
    }

    /**
     * @param ProductToOption $productToOption
     * @return float
     */
    public function getQuantity($productToOption)
    {
        return $productToOption->quantity;
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function getQuoteClass($productToOption)
    {
        if ($productToOption->quote_class) {
            return $productToOption->quote_class;
        }
        $component = $this->getComponent($productToOption);
        if ($component) {
            if ($component->quote_class) {
                return $component->quote_class;
            }
            //return $component->componentType->quote_class;
        }
        return '';
    }

    /**
     * @param ProductToOption $productToOption
     * @return string
     */
    public function getQuoteLabel($productToOption)
    {
        $quoteClass = $this->getQuoteClass($productToOption);
        if (!$quoteClass) {
            return '';
        }
        /** @var BaseComponentQuote $componentQuote */
        $componentQuote = new $quoteClass;
        $component = $this->getComponent($productToOption);
        return '<span title="' . Html::encode($componentQuote->getDescription($component, $productToOption->item)) . '" data-toggle="tooltip">' . BaseComponentQuote::opts()[$componentQuote->className()] . '</span>';
    }

    /**
     * @param ProductToOption $productToOption
     * @return \app\models\Component
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            if (!empty($productToOption->getValueDecoded())) {
                $this->_component = Component::findOne($productToOption->getValueDecoded());
            }
        }
        return $this->_component;
    }

    /**
     * @param ProductToOption $productToOption
     * @param bool $verbose
     * @throws Exception
     */
    public function saveQuote($productToOption, $verbose = false)
    {
        if ($productToOption->quote_generated) {
            return;
        }

        // save ProductToOption quote
        $productToOption->quote_label = $this->getQuoteLabel($productToOption);
        $productToOption->quote_quantity = $this->getQuoteQuantity($productToOption);
        $productToOption->quote_generated = 1;
        if ($productToOption->quote_quantity > 0) {
            $productToOption->quote_make_ready_cost = $this->getMakeReadyQuote($productToOption);
            $productToOption->quote_minimum_cost = $this->getQuoteMinimumCost($productToOption);
            $productToOption->quote_unit_cost = $this->getUnitQuote($productToOption);
            $productToOption->quote_total_cost = $this->getQuoteTotalCost($productToOption);
            $productToOption->quote_factor = $this->getQuoteFactor($productToOption);
            $productToOption->quote_total_price = $this->getQuoteTotalPrice($productToOption);
            $productToOption->quote_weight = 0; //$this->getQuoteWeight($productToOption) * $productToOption->quote_quantity;
        } else {
            $productToOption->quote_make_ready_cost = 0;
            $productToOption->quote_minimum_cost = 0;
            $productToOption->quote_unit_cost = 0;
            $productToOption->quote_total_cost = 0;
            $productToOption->quote_factor = 0;
            $productToOption->quote_total_price = 0;
            $productToOption->quote_weight = 0;
        }
        if (!$productToOption->save(false)) {
            throw new Exception('Cannot save productToOption-' . $productToOption->id . ': ' . Helper::getErrorString($productToOption));
        }
        if ($verbose) {
            echo 'O';
        }
    }

}