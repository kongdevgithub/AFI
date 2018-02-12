<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "industry".
 *
 * @property integer $id
 * @property string $name
 * @property integer $deleted_at
 * @property integer $market_phase
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Company[] $companies
 */
class Industry extends ActiveRecord
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
        return 'industry';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'deleted_at', 'market_phase', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'deleted_at', 'market_phase', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'deleted_at', 'market_phase', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'market_phase'], 'required'],
            [['deleted_at', 'market_phase'], 'integer'],
            [['name'], 'string', 'max' => 255]
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
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'market_phase' => Yii::t('models', 'Market Phase'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(\app\models\Company::className(), ['industry_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\IndustryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\IndustryQuery(get_called_class());
    }

}
