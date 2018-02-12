<?php

namespace app\models;

use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "product_to_component".
 *
 * @property float $quantity
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
 * @mixin CacheBehavior
 */
class ProductToComponent extends base\ProductToComponent
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['product', 'item'],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'product_id', 'item_id', 'component_id', 'product_type_to_component_id', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
            'create' => ['id', 'product_id', 'item_id', 'component_id', 'product_type_to_component_id', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
            'update' => ['id', 'product_id', 'item_id', 'component_id', 'product_type_to_component_id', 'quantity', 'sort_order', 'quote_class', 'quote_label', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_quantity_factor', 'quote_weight', 'quote_generated', 'deleted_at', 'created_at', 'updated_at', 'checked_quantity'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['product_id', 'component_id', 'product_type_to_component_id', 'sort_order', 'quote_generated', 'deleted_at'], 'integer'],
            [['component_id'], 'required'],
            [['quantity', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_weight'], 'number'],
            [['quote_quantity_factor'], 'string'],
            [['quote_class', 'quote_label'], 'string', 'max' => 255],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => Component::className(), 'targetAttribute' => ['component_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_type_to_component_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductTypeToComponent::className(), 'targetAttribute' => ['product_type_to_component_id' => 'id']]
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['product_id'] = Yii::t('app', 'Product');
        $attributeLabels['component_id'] = Yii::t('app', 'Component');
        return $attributeLabels;
    }

    /**
     * @return string
     */
    public function getQuoteClass()
    {
        if ($this->quote_class) {
            return $this->quote_class;
        }
        //if ($this->component->quote_class) {
        return $this->component->quote_class;
        //}
        //return $this->component->componentType->quote_class;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // sanitize data
        $this->quote_quantity = round($this->quote_quantity, 4);
        $this->quote_total_cost = round($this->quote_total_cost, 4);
        $this->quote_total_price = round($this->quote_total_price, 4);
        $this->quote_make_ready_cost = round($this->quote_make_ready_cost, 4);

        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function getName()
    {
        /** @var BaseComponentQuote $componentQuote */
        $componentQuote = new $this->quote_class;
        return $componentQuote->getName($this->component);
    }

    /**
     *
     */
    public function resetQuoteGenerated()
    {
        $this->quote_generated = 0;
        if (!$this->save(false)) {
            throw new Exception('Cannot save productToComponent-' . $this->id . ': ' . Helper::getErrorString($this));
        }
    }

    /**
     * @param array $attributes
     * @return ProductToComponent|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $productToComponent = new ProductToComponent();
        $productToComponent->loadDefaultValues();
        $productToComponent->attributes = $this->attributes;
        $productToComponent->id = null;
        $productToComponent->product_id = $attributes['ProductToComponent']['product_id'];
        $allowedAttributes = [
            'item_id',
        ];
        if (!empty($attributes['ProductToComponent'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['ProductToComponent'])) {
                    $productToComponent->$attribute = $attributes['ProductToComponent'][$attribute];
                }
            }
        }
        $validate = $this->quote_quantity == 0 ? false : true;
        if (!$productToComponent->save($validate)) {
            throw new Exception('cannot copy ProductToComponent-' . $this->id . ': ' . Helper::getErrorString($productToComponent));
        }
        return $productToComponent;
    }
}
