<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "job_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Company[] $companies
 * @property \app\models\Job[] $jobs
 */
class JobType extends ActiveRecord
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
        return 'job_type';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'sort_order', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['sort_order', 'deleted_at'], 'integer'],
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
            'sort_order' => Yii::t('models', 'Sort Order'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(\app\models\Company::className(), ['job_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(\app\models\Job::className(), ['job_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\JobTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\JobTypeQuery(get_called_class());
    }

}
