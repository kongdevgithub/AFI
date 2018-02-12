<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "company_rate".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $product_type_id
 * @property integer $item_type_id
 * @property integer $option_id
 * @property integer $component_id
 * @property string $size
 * @property string $price
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Company $company
 * @property \app\models\ProductType $productType
 * @property \app\models\ItemType $itemType
 * @property \app\models\Option $option
 * @property \app\models\Component $component
 * @property \app\models\CompanyRateOption[] $companyRateOptions
 */
class CompanyRate extends ActiveRecord
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
        return 'company_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'product_type_id', 'item_type_id', 'option_id', 'component_id', 'price'], 'required'],
            [['company_id', 'product_type_id', 'item_type_id', 'option_id', 'component_id', 'deleted_at'], 'integer'],
            [['price'], 'number'],
            [['size'], 'string', 'max' => 64],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']],
            [['item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ItemType::className(), 'targetAttribute' => ['item_type_id' => 'id']],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Option::className(), 'targetAttribute' => ['option_id' => 'id']],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Component::className(), 'targetAttribute' => ['component_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'company_id' => Yii::t('models', 'Company ID'),
            'product_type_id' => Yii::t('models', 'Product Type ID'),
            'item_type_id' => Yii::t('models', 'Item Type ID'),
            'option_id' => Yii::t('models', 'Option ID'),
            'component_id' => Yii::t('models', 'Component ID'),
            'size' => Yii::t('models', 'Size'),
            'price' => Yii::t('models', 'Price'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'company_id']);
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
    public function getItemType()
    {
        return $this->hasOne(\app\models\ItemType::className(), ['id' => 'item_type_id']);
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
    public function getComponent()
    {
        return $this->hasOne(\app\models\Component::className(), ['id' => 'component_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRateOptions()
    {
        return $this->hasMany(\app\models\CompanyRateOption::className(), ['company_rate_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\CompanyRateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CompanyRateQuery(get_called_class());
    }

}
