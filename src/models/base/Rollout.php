<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "rollout".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $status
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Job[] $jobs
 * @property \app\models\Company $company
 */
class Rollout extends ActiveRecord
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
        return 'rollout';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'company_id', 'name', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'company_id', 'name', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'company_id', 'name', 'status', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'name'], 'required'],
            [['company_id', 'deleted_at'], 'integer'],
            [['name', 'status'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']]
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
            'name' => Yii::t('models', 'Name'),
            'status' => Yii::t('models', 'Status'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(\app\models\Job::className(), ['rollout_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(\app\models\Company::className(), ['id' => 'company_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\RolloutQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\RolloutQuery(get_called_class());
    }

}
