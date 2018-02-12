<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "item".
 *
 * @property integer $id
 * @property string $name
 * @property integer $product_id
 * @property integer $item_type_id
 * @property integer $split_id
 * @property string $quote_class
 * @property integer $product_type_to_item_type_id
 * @property integer $quantity
 * @property integer $supplier_id
 * @property string $purchase_order
 * @property string $supply_date
 * @property string $due_date
 * @property string $status
 * @property integer $sort_order
 * @property string $quote_label
 * @property string $quote_unit_price
 * @property string $quote_quantity
 * @property string $quote_total_price
 * @property string $quote_total_price_unlocked
 * @property string $quote_unit_cost
 * @property string $quote_total_cost
 * @property string $quote_factor
 * @property string $quote_factor_price
 * @property string $quote_weight
 * @property integer $quote_generated
 * @property integer $deleted_at
 * @property integer $production_at
 * @property integer $order_at
 * @property integer $despatch_at
 * @property integer $complete_at
 * @property integer $packed_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\ItemType $itemType
 * @property \app\models\Product $product
 * @property \app\models\ProductTypeToItemType $productTypeToItemType
 * @property \app\models\Company $supplier
 * @property \app\models\Item $split
 * @property \app\models\Item[] $items
 * @property \app\models\ItemToAddress[] $itemToAddresses
 * @property \app\models\ItemToComponentCheck[] $itemToComponentChecks
 * @property \app\models\ItemToMachine[] $itemToMachines
 * @property \app\models\ProductToComponent[] $productToComponents
 * @property \app\models\ProductToOption[] $productToOptions
 * @property \app\models\Unit[] $units
 */
class Item extends ActiveRecord
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
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'product_id', 'item_type_id', 'quote_class', 'quantity'], 'required'],
            [['product_id', 'item_type_id', 'split_id', 'product_type_to_item_type_id', 'quantity', 'supplier_id', 'sort_order', 'quote_generated', 'deleted_at', 'production_at', 'order_at', 'despatch_at', 'complete_at', 'packed_at'], 'integer'],
            [['supply_date', 'due_date'], 'safe'],
            [['quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_weight'], 'number'],
            [['name', 'quote_class', 'purchase_order', 'status', 'quote_label'], 'string', 'max' => 255],
            [['item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ItemType::className(), 'targetAttribute' => ['item_type_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_type_to_item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductTypeToItemType::className(), 'targetAttribute' => ['product_type_to_item_type_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['supplier_id' => 'id']],
            [['split_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['split_id' => 'id']]
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
            'product_id' => Yii::t('models', 'Product ID'),
            'item_type_id' => Yii::t('models', 'Item Type ID'),
            'split_id' => Yii::t('models', 'Split ID'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'product_type_to_item_type_id' => Yii::t('models', 'Product Type To Item Type ID'),
            'quantity' => Yii::t('models', 'Quantity'),
            'supplier_id' => Yii::t('models', 'Supplier ID'),
            'purchase_order' => Yii::t('models', 'Purchase Order'),
            'supply_date' => Yii::t('models', 'Supply Date'),
            'due_date' => Yii::t('models', 'Due Date'),
            'status' => Yii::t('models', 'Status'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quote_label' => Yii::t('models', 'Quote Label'),
            'quote_unit_price' => Yii::t('models', 'Quote Unit Price'),
            'quote_quantity' => Yii::t('models', 'Quote Quantity'),
            'quote_total_price' => Yii::t('models', 'Quote Total Price'),
            'quote_total_price_unlocked' => Yii::t('models', 'Quote Total Price Unlocked'),
            'quote_unit_cost' => Yii::t('models', 'Quote Unit Cost'),
            'quote_total_cost' => Yii::t('models', 'Quote Total Cost'),
            'quote_factor' => Yii::t('models', 'Quote Factor'),
            'quote_factor_price' => Yii::t('models', 'Quote Factor Price'),
            'quote_weight' => Yii::t('models', 'Quote Weight'),
            'quote_generated' => Yii::t('models', 'Quote Generated'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'production_at' => Yii::t('models', 'Production At'),
            'order_at' => Yii::t('models', 'Order At'),
            'despatch_at' => Yii::t('models', 'Despatch At'),
            'complete_at' => Yii::t('models', 'Complete At'),
            'packed_at' => Yii::t('models', 'Packed At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemType()
    {
        return $this->hasOne(\app\models\ItemType::className(), ['id' => 'item_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(\app\models\Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToItemType()
    {
        return $this->hasOne(\app\models\ProductTypeToItemType::className(), ['id' => 'product_type_to_item_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'supplier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSplit()
    {
        return $this->hasOne(\app\models\Item::className(), ['id' => 'split_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(\app\models\Item::className(), ['split_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToAddresses()
    {
        return $this->hasMany(\app\models\ItemToAddress::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToComponentChecks()
    {
        return $this->hasMany(\app\models\ItemToComponentCheck::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToMachines()
    {
        return $this->hasMany(\app\models\ItemToMachine::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(\app\models\ProductToComponent::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(\app\models\ProductToOption::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnits()
    {
        return $this->hasMany(\app\models\Unit::className(), ['item_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ItemQuery(get_called_class());
    }

}
