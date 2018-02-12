<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_type_to_option".
 *
 * @property integer $id
 * @property integer $product_type_id
 * @property integer $product_type_to_item_type_id
 * @property integer $option_id
 * @property string $values
 * @property integer $required
 * @property integer $describes_item
 * @property integer $sort_order
 * @property string $quote_class
 * @property string $quantity_factor
 * @property string $config
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\ProductToOption[] $productToOptions
 * @property \app\models\Option $option
 * @property \app\models\ProductType $productType
 * @property \app\models\ProductTypeToItemType $productTypeToItemType
 */
class ProductTypeToOption extends ActiveRecord
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
        return 'product_type_to_option';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'option_id', 'values', 'required', 'describes_item', 'sort_order', 'quote_class', 'quantity_factor', 'config', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'option_id', 'values', 'required', 'describes_item', 'sort_order', 'quote_class', 'quantity_factor', 'config', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'product_type_id', 'product_type_to_item_type_id', 'option_id', 'values', 'required', 'describes_item', 'sort_order', 'quote_class', 'quantity_factor', 'config', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_type_id', 'option_id'], 'required'],
            [['product_type_id', 'product_type_to_item_type_id', 'option_id', 'required', 'describes_item', 'sort_order', 'deleted_at'], 'integer'],
            [['values', 'quantity_factor', 'config'], 'string'],
            [['quote_class'], 'string', 'max' => 255],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Option::className(), 'targetAttribute' => ['option_id' => 'id']],
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
            'option_id' => Yii::t('models', 'Option ID'),
            'values' => Yii::t('models', 'Values'),
            'required' => Yii::t('models', 'Required'),
            'describes_item' => Yii::t('models', 'Describes Item'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'quantity_factor' => Yii::t('models', 'Quantity Factor'),
            'config' => Yii::t('models', 'Config'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(\app\models\ProductToOption::className(), ['product_type_to_option_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(\app\models\Option::className(), ['id' => 'option_id']);
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
     * @return \app\models\query\ProductTypeToOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductTypeToOptionQuery(get_called_class());
    }

}
