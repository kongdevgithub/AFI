<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_type_to_item_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $product_type_id
 * @property integer $item_type_id
 * @property integer $quantity
 * @property integer $sort_order
 * @property string $quote_class
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Item[] $items
 * @property \app\models\ProductTypeToComponent[] $productTypeToComponents
 * @property \app\models\ItemType $itemType
 * @property \app\models\ProductType $productType
 * @property \app\models\ProductTypeToOption[] $productTypeToOptions
 */
class ProductTypeToItemType extends ActiveRecord
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
        return 'product_type_to_item_type';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'product_type_id', 'item_type_id', 'quantity', 'sort_order', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'product_type_id', 'item_type_id', 'quantity', 'sort_order', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'product_type_id', 'item_type_id', 'quantity', 'sort_order', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'product_type_id', 'item_type_id', 'quantity', 'quote_class'], 'required'],
            [['product_type_id', 'item_type_id', 'quantity', 'sort_order', 'deleted_at'], 'integer'],
            [['name', 'quote_class'], 'string', 'max' => 255],
            [['item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ItemType::className(), 'targetAttribute' => ['item_type_id' => 'id']],
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
            'product_type_id' => Yii::t('models', 'Product Type ID'),
            'item_type_id' => Yii::t('models', 'Item Type ID'),
            'quantity' => Yii::t('models', 'Quantity'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(\app\models\Item::className(), ['product_type_to_item_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(\app\models\ProductTypeToComponent::className(), ['product_type_to_item_type_id' => 'id']);
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
    public function getProductType()
    {
        return $this->hasOne(\app\models\ProductType::className(), ['id' => 'product_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(\app\models\ProductTypeToOption::className(), ['product_type_to_item_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductTypeToItemTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductTypeToItemTypeQuery(get_called_class());
    }

}
