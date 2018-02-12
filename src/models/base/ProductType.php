<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_type".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $image
 * @property string $quote_class
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $complexity
 * @property string $config
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\CompanyRate[] $companyRates
 * @property \app\models\Product[] $products
 * @property \app\models\ProductType $parent
 * @property \app\models\ProductType[] $productTypes
 * @property \app\models\ProductTypeToComponent[] $productTypeToComponents
 * @property \app\models\ProductTypeToItemType[] $productTypeToItemTypes
 * @property \app\models\ProductTypeToOption[] $productTypeToOptions
 */
class ProductType extends ActiveRecord
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
        return 'product_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order', 'deleted_at', 'complexity'], 'integer'],
            [['name', 'quote_class', 'sort_order', 'complexity'], 'required'],
            [['config'], 'string'],
            [['name', 'image', 'quote_class'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductType::className(), 'targetAttribute' => ['parent_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'parent_id' => Yii::t('models', 'Parent ID'),
            'name' => Yii::t('models', 'Name'),
            'image' => Yii::t('models', 'Image'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'complexity' => Yii::t('models', 'Complexity'),
            'config' => Yii::t('models', 'Config'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRates()
    {
        return $this->hasMany(\app\models\CompanyRate::className(), ['product_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\models\Product::className(), ['product_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(\app\models\ProductType::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypes()
    {
        return $this->hasMany(\app\models\ProductType::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(\app\models\ProductTypeToComponent::className(), ['product_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToItemTypes()
    {
        return $this->hasMany(\app\models\ProductTypeToItemType::className(), ['product_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(\app\models\ProductTypeToOption::className(), ['product_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductTypeQuery(get_called_class());
    }

}
