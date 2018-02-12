<?php

namespace app\models;

use app\components\fields\BaseField;
use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "product_to_option".
 *
 * @property float $quote_unit_cost
 * @property float $quote_quantity
 * @property float $quote_total_cost
 * @property float $quote_make_ready_cost
 * @property float $quote_factor
 * @property float $quote_total_price
 * @property float $quote_minimum_cost
 * @property float $quote_weight
 * @property float $checked_quantity
 *
 * @property string|array $valueDecoded
 *
 * @mixin CacheBehavior
 */
class ProductToOption extends base\ProductToOption
{
    /**
     * @var
     */
    private $_valueDecoded;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['product', 'item'],
        ];
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = [
            'default' => ['id', 'product_id', 'item_id', 'option_id', 'product_type_to_option_id', 'value', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
            'create' => ['id', 'product_id', 'item_id', 'option_id', 'product_type_to_option_id', 'value', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
            'update' => ['id', 'product_id', 'item_id', 'option_id', 'product_type_to_option_id', 'value', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
        ];
        foreach ($scenarios as $k => $scenario) {
            $scenarios[$k][] = 'valueDecoded';
        }
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['product_id', 'option_id', 'product_type_to_option_id', 'sort_order', 'quote_generated', 'deleted_at'], 'integer'],
            [['option_id'], 'required'],
            [['quote_quantity_factor'], 'string'],
            [['quantity', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_weight'], 'number'],
            [['quote_class', 'quote_label'], 'string', 'max' => 255],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => Option::className(), 'targetAttribute' => ['option_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_type_to_option_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductTypeToOption::className(), 'targetAttribute' => ['product_type_to_option_id' => 'id']]
        ];

        // option rules
        if ($this->option) {
            /** @var BaseField $baseField */
            $baseField = new $this->option->field_class;
            foreach ($baseField->rulesProduct($this) as $rule) {
                $rules[] = $rule;
            }
        }

        $rules[] = [['quantity'], 'required'];
        $rules[] = [['quantity'], 'number', 'min' => 0];

        $rules[] = [['valueDecoded'], 'safe'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['product_id'] = Yii::t('app', 'Product');
        $attributeLabels['option_id'] = Yii::t('app', 'Option');
        $attributeLabels['valueDecoded'] = $this->option ? $this->option->name : Yii::t('app', 'Value');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        //$this->value = Json::decode($this->value);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //$this->value = Json::encode($this->value);

        // sanitize data
        $this->quote_unit_cost = round($this->quote_unit_cost, 4);
        $this->quote_total_cost = round($this->quote_total_cost, 4);
        $this->quote_total_price = round($this->quote_total_price, 4);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        //$this->value = Json::decode($this->value);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return array|string|null
     */
    public function getValueDecoded()
    {
        if (!$this->_valueDecoded) {
            $this->_valueDecoded = Json::decode($this->value);
        }
        return $this->_valueDecoded;
    }

    /**
     * @param array|null $valueDecoded
     */
    public function setValueDecoded($valueDecoded)
    {
        $this->value = Json::encode($valueDecoded, JSON_PRETTY_PRINT);
        $this->_valueDecoded = Json::decode($this->value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        /** @var BaseField $field */
        $field = new $this->option->field_class;
        return $field->nameProduct($this);
    }

    /**
     *
     */
    public function resetQuoteGenerated()
    {
        $this->quote_generated = 0;
        if (!$this->save(false)) {
            throw new Exception('Cannot save productToOption-' . $this->id . ': ' . Helper::getErrorString($this));
        }
    }

    /**
     * @param array $attributes
     * @return ProductToOption|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $productToOption = new ProductToOption();
        $productToOption->loadDefaultValues();
        $productToOption->attributes = $this->attributes;
        $productToOption->id = null;
        $productToOption->product_id = $attributes['ProductToOption']['product_id'];
        $allowedAttributes = [
            'item_id',
        ];
        if (isset($attributes['ProductToOption'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['ProductToOption'])) {
                    $productToOption->$attribute = $attributes['ProductToOption'][$attribute];
                }
            }
        }
        $validate = $this->quote_quantity == 0 ? false : true;
        if (!$productToOption->save($validate)) {
            throw new Exception('cannot copy ProductToOption-' . $this->id . ': ' . Helper::getErrorString($productToOption));
        }
        return $productToOption;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->quantity === null)) {
            if ($this->option_id == Option::OPTION_EM_PRINT) {
                $this->quantity = 0;
            } else {
                $this->quantity = 1;
            }
        }
        return $this;
    }

}
