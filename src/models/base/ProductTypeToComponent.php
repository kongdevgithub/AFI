<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_type_to_component".
 *
 * @property integer $id
 * @property integer $product_type_id
 * @property integer $product_type_to_item_type_id
 * @property integer $component_id
 * @property string $quote_class
 * @property string $quantity
 * @property integer $sort_order
 * @property string $quantity_factor
 * @property integer $describes_item
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\ProductToComponent[] $productToComponents
 * @property \app\models\Component $component
 * @property \app\models\ProductType $productType
 * @property \app\models\ProductTypeToItemType $productTypeToItemType
 */
class ProductTypeToComponent extends ActiveRecord
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
        return 'product_type_to_component';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'component_id', 'quote_class', 'quantity', 'sort_order', 'quantity_factor', 'describes_item', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'component_id', 'quote_class', 'quantity', 'sort_order', 'quantity_factor', 'describes_item', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'component_id', 'quote_class', 'quantity', 'sort_order', 'quantity_factor', 'describes_item', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_type_id', 'product_type_to_item_type_id', 'component_id', 'quantity'], 'required'],
            [['product_type_id', 'product_type_to_item_type_id', 'component_id', 'sort_order', 'describes_item', 'deleted_at'], 'integer'],
            [['quantity'], 'number'],
            [['quantity_factor'], 'string'],
            [['quote_class'], 'string', 'max' => 255],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Component::className(), 'targetAttribute' => ['component_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']],
            [['product_type_to_item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductTypeToItemType::className(), 'targetAttribute' => ['product_type_to_item_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'product_type_id' => Yii::t('models', 'Product Type ID'),
            'product_type_to_item_type_id' => Yii::t('models', 'Product Type To Item Type ID'),
            'component_id' => Yii::t('models', 'Component ID'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'quantity' => Yii::t('models', 'Quantity'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quantity_factor' => Yii::t('models', 'Quantity Factor'),
            'describes_item' => Yii::t('models', 'Describes Item'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(\app\models\ProductToComponent::className(), ['product_type_to_component_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComponent()
    {
        return $this->hasOne(\app\models\Component::className(), ['id' => 'component_id']);
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
    public function getProductTypeToItemType()
    {
        return $this->hasOne(\app\models\ProductTypeToItemType::className(), ['id' => 'product_type_to_item_type_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductTypeToComponentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductTypeToComponentQuery(get_called_class());
    }

}
