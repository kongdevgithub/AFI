<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product".
 *
 * @property integer $id
 * @property string $name
 * @property string $details
 * @property integer $job_id
 * @property integer $fork_quantity_product_id
 * @property integer $product_type_id
 * @property integer $sort_order
 * @property string $quote_class
 * @property integer $quantity
 * @property string $status
 * @property string $quote_label
 * @property string $quote_unit_price
 * @property string $quote_quantity
 * @property string $quote_total_price
 * @property string $quote_total_price_unlocked
 * @property string $quote_unit_cost
 * @property string $quote_total_cost
 * @property string $quote_factor
 * @property string $quote_factor_price
 * @property string $quote_discount_price
 * @property string $quote_weight
 * @property integer $quote_generated
 * @property integer $deleted_at
 * @property integer $production_at
 * @property integer $despatch_at
 * @property integer $complete_at
 * @property integer $complexity
 * @property integer $quote_hide_item_description
 * @property string $due_date
 * @property integer $prebuild_required
 * @property integer $preserve_unit_prices
 * @property integer $packed_at
 * @property integer $prevent_rate_prices
 * @property integer $bulk_component
 * @property string $quote_retail_unit_price_import
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Item[] $items
 * @property \app\models\Product $forkQuantityProduct
 * @property \app\models\Product[] $products
 * @property \app\models\Job $job
 * @property \app\models\ProductType $productType
 * @property \app\models\ProductToAddress[] $productToAddresses
 * @property \app\models\ProductToComponent[] $productToComponents
 * @property \app\models\ProductToOption[] $productToOptions
 */
class Product extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbData;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'job_id', 'product_type_id', 'quote_class', 'quantity', 'status'], 'required'],
            [['details'], 'string'],
            [['job_id', 'fork_quantity_product_id', 'product_type_id', 'sort_order', 'quantity', 'quote_generated', 'deleted_at', 'production_at', 'despatch_at', 'complete_at', 'complexity', 'quote_hide_item_description', 'prebuild_required', 'preserve_unit_prices', 'packed_at', 'prevent_rate_prices', 'bulk_component'], 'integer'],
            [['quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_discount_price', 'quote_weight', 'quote_retail_unit_price_import'], 'number'],
            [['due_date'], 'safe'],
            [['name', 'quote_class', 'status', 'quote_label'], 'string', 'max' => 255],
            [['fork_quantity_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Product::className(), 'targetAttribute' => ['fork_quantity_product_id' => 'id']],
            [['job_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Job::className(), 'targetAttribute' => ['job_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'name' => Yii::t('models', 'Name'),
            'details' => Yii::t('models', 'Details'),
            'job_id' => Yii::t('models', 'Job ID'),
            'fork_quantity_product_id' => Yii::t('models', 'Fork Quantity Product ID'),
            'product_type_id' => Yii::t('models', 'Product Type ID'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'quantity' => Yii::t('models', 'Quantity'),
            'status' => Yii::t('models', 'Status'),
            'quote_label' => Yii::t('models', 'Quote Label'),
            'quote_unit_price' => Yii::t('models', 'Quote Unit Price'),
            'quote_quantity' => Yii::t('models', 'Quote Quantity'),
            'quote_total_price' => Yii::t('models', 'Quote Total Price'),
            'quote_total_price_unlocked' => Yii::t('models', 'Quote Total Price Unlocked'),
            'quote_unit_cost' => Yii::t('models', 'Quote Unit Cost'),
            'quote_total_cost' => Yii::t('models', 'Quote Total Cost'),
            'quote_factor' => Yii::t('models', 'Quote Factor'),
            'quote_factor_price' => Yii::t('models', 'Quote Factor Price'),
            'quote_discount_price' => Yii::t('models', 'Quote Discount Price'),
            'quote_weight' => Yii::t('models', 'Quote Weight'),
            'quote_generated' => Yii::t('models', 'Quote Generated'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'production_at' => Yii::t('models', 'Production At'),
            'despatch_at' => Yii::t('models', 'Despatch At'),
            'complete_at' => Yii::t('models', 'Complete At'),
            'complexity' => Yii::t('models', 'Complexity'),
            'quote_hide_item_description' => Yii::t('models', 'Quote Hide Item Description'),
            'due_date' => Yii::t('models', 'Due Date'),
            'prebuild_required' => Yii::t('models', 'Prebuild Required'),
            'preserve_unit_prices' => Yii::t('models', 'Preserve Unit Prices'),
            'packed_at' => Yii::t('models', 'Packed At'),
            'prevent_rate_prices' => Yii::t('models', 'Prevent Rate Prices'),
            'bulk_component' => Yii::t('models', 'Bulk Component'),
            'quote_retail_unit_price_import' => Yii::t('models', 'Quote Retail Unit Price Import'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(\app\models\Item::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForkQuantityProduct()
    {
        return $this->hasOne(\app\models\Product::className(), ['id' => 'fork_quantity_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\models\Product::className(), ['fork_quantity_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(\app\models\Job::className(), ['id' => 'job_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(\app\models\ProductType::className(), ['id' => 'product_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToAddresses()
    {
        return $this->hasMany(\app\models\ProductToAddress::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(\app\models\ProductToComponent::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(\app\models\ProductToOption::className(), ['product_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductQuery(get_called_class());
    }

}
