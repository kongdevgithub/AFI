<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "company_rate_option".
 *
 * @property integer $id
 * @property integer $company_rate_id
 * @property integer $option_id
 * @property integer $component_id
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\CompanyRate $companyRate
 * @property \app\models\Option $option
 * @property \app\models\Component $component
 */
class CompanyRateOption extends ActiveRecord
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
        return 'company_rate_option';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_rate_id', 'option_id', 'component_id'], 'required'],
            [['company_rate_id', 'option_id', 'component_id', 'deleted_at'], 'integer'],
            [['company_rate_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\CompanyRate::className(), 'targetAttribute' => ['company_rate_id' => 'id']],
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
            'company_rate_id' => Yii::t('models', 'Company Rate ID'),
            'option_id' => Yii::t('models', 'Option ID'),
            'component_id' => Yii::t('models', 'Component ID'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRate()
    {
        return $this->hasOne(\app\models\CompanyRate::className(), ['id' => 'company_rate_id']);
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
     * @inheritdoc
     * @return \app\models\query\CompanyRateOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CompanyRateOptionQuery(get_called_class());
    }

}
