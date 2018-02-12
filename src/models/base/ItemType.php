<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "item_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $quote_class
 * @property string $color
 * @property integer $virtual
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\CompanyRate[] $companyRates
 * @property \app\models\Item[] $items
 * @property \app\models\MachineType[] $machineTypes
 * @property \app\models\ProductTypeToItemType[] $productTypeToItemTypes
 */
class ItemType extends ActiveRecord
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
        return 'item_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'quote_class'], 'required'],
            [['virtual', 'sort_order', 'deleted_at'], 'integer'],
            [['name', 'quote_class', 'color'], 'string', 'max' => 255]
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
            'quote_class' => Yii::t('models', 'Quote Class'),
            'color' => Yii::t('models', 'Color'),
            'virtual' => Yii::t('models', 'Virtual'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRates()
    {
        return $this->hasMany(\app\models\CompanyRate::className(), ['item_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(\app\models\Item::className(), ['item_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMachineTypes()
    {
        return $this->hasMany(\app\models\MachineType::className(), ['item_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToItemTypes()
    {
        return $this->hasMany(\app\models\ProductTypeToItemType::className(), ['item_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ItemTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ItemTypeQuery(get_called_class());
    }

}
