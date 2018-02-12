<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "option".
 *
 * @property integer $id
 * @property string $name
 * @property string $field_class
 * @property string $field_config
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\CompanyRate[] $companyRates
 * @property \app\models\CompanyRateOption[] $companyRateOptions
 * @property \app\models\ProductToOption[] $productToOptions
 * @property \app\models\ProductTypeToOption[] $productTypeToOptions
 */
class Option extends ActiveRecord
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
        return 'option';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'field_class'], 'required'],
            [['field_config'], 'string'],
            [['deleted_at'], 'integer'],
            [['name', 'field_class'], 'string', 'max' => 255]
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
            'field_class' => Yii::t('models', 'Field Class'),
            'field_config' => Yii::t('models', 'Field Config'),
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
        return $this->hasMany(\app\models\CompanyRate::className(), ['option_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRateOptions()
    {
        return $this->hasMany(\app\models\CompanyRateOption::className(), ['option_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(\app\models\ProductToOption::className(), ['option_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(\app\models\ProductTypeToOption::className(), ['option_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\OptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\OptionQuery(get_called_class());
    }

}
