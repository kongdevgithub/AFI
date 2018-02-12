<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "component".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $component_type_id
 * @property string $brand
 * @property string $status
 * @property string $unit_cost
 * @property string $quantity_factor
 * @property string $component_config
 * @property string $quote_class
 * @property string $make_ready_cost
 * @property string $minimum_cost
 * @property string $unit_weight
 * @property string $unit_dead_weight
 * @property string $unit_cubic_weight
 * @property string $unit_of_measure
 * @property integer $track_stock
 * @property integer $quality_check
 * @property string $quality_code
 * @property string $notes
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\CompanyRate[] $companyRates
 * @property \app\models\CompanyRateOption[] $companyRateOptions
 * @property \app\models\ComponentType $componentType
 * @property \app\models\ItemToComponentCheck[] $itemToComponentChecks
 * @property \app\models\ProductToComponent[] $productToComponents
 * @property \app\models\ProductTypeToComponent[] $productTypeToComponents
 */
class Component extends ActiveRecord
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
        return 'component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name', 'component_type_id', 'unit_cost', 'quantity_factor', 'quote_class', 'quality_code'], 'required'],
            [['component_type_id', 'track_stock', 'quality_check', 'deleted_at'], 'integer'],
            [['unit_cost', 'make_ready_cost', 'minimum_cost', 'unit_weight', 'unit_dead_weight', 'unit_cubic_weight'], 'number'],
            [['quantity_factor', 'component_config', 'notes'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['name', 'brand', 'status', 'quote_class', 'quality_code'], 'string', 'max' => 255],
            [['unit_of_measure'], 'string', 'max' => 16],
            [['component_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ComponentType::className(), 'targetAttribute' => ['component_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'code' => Yii::t('models', 'Code'),
            'name' => Yii::t('models', 'Name'),
            'component_type_id' => Yii::t('models', 'Component Type ID'),
            'brand' => Yii::t('models', 'Brand'),
            'status' => Yii::t('models', 'Status'),
            'unit_cost' => Yii::t('models', 'Unit Cost'),
            'quantity_factor' => Yii::t('models', 'Quantity Factor'),
            'component_config' => Yii::t('models', 'Component Config'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'make_ready_cost' => Yii::t('models', 'Make Ready Cost'),
            'minimum_cost' => Yii::t('models', 'Minimum Cost'),
            'unit_weight' => Yii::t('models', 'Unit Weight'),
            'unit_dead_weight' => Yii::t('models', 'Unit Dead Weight'),
            'unit_cubic_weight' => Yii::t('models', 'Unit Cubic Weight'),
            'unit_of_measure' => Yii::t('models', 'Unit Of Measure'),
            'track_stock' => Yii::t('models', 'Track Stock'),
            'quality_check' => Yii::t('models', 'Quality Check'),
            'quality_code' => Yii::t('models', 'Quality Code'),
            'notes' => Yii::t('models', 'Notes'),
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
        return $this->hasMany(\app\models\CompanyRate::className(), ['component_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRateOptions()
    {
        return $this->hasMany(\app\models\CompanyRateOption::className(), ['component_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComponentType()
    {
        return $this->hasOne(\app\models\ComponentType::className(), ['id' => 'component_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToComponentChecks()
    {
        return $this->hasMany(\app\models\ItemToComponentCheck::className(), ['component_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(\app\models\ProductToComponent::className(), ['component_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(\app\models\ProductTypeToComponent::className(), ['component_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ComponentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ComponentQuery(get_called_class());
    }

}
