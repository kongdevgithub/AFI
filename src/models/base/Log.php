<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "log".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $message
 * @property integer $audit_entry_id
 * @property integer $created_by
 * @property integer $created_at
 */
class Log extends ActiveRecord
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
        return 'log';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'message', 'audit_entry_id', 'created_by', 'created_at'],
            'create' => ['id', 'model_name', 'model_id', 'message', 'audit_entry_id', 'created_by', 'created_at'],
            'update' => ['id', 'model_name', 'model_id', 'message', 'audit_entry_id', 'created_by', 'created_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'audit_entry_id'], 'integer'],
            [['message', 'audit_entry_id'], 'required'],
            [['message'], 'string'],
            [['model_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'model_name' => Yii::t('models', 'Model Name'),
            'model_id' => Yii::t('models', 'Model ID'),
            'message' => Yii::t('models', 'Message'),
            'created_by' => Yii::t('models', 'Created By'),
            'created_at' => Yii::t('models', 'Created At'),
            'audit_entry_id' => Yii::t('models', 'Audit Entry ID'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\LogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\LogQuery(get_called_class());
    }

}
