<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "notification".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $title
 * @property string $body
 * @property string $type
 * @property integer $deleted_at
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 */
class Notification extends ActiveRecord
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
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'title', 'body', 'type', 'deleted_at', 'created_by', 'created_at', 'updated_by', 'updated_at'],
            'create' => ['id', 'model_name', 'model_id', 'title', 'body', 'type', 'deleted_at', 'created_by', 'created_at', 'updated_by', 'updated_at'],
            'update' => ['id', 'model_name', 'model_id', 'title', 'body', 'type', 'deleted_at', 'created_by', 'created_at', 'updated_by', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'title', 'type'], 'required'],
            [['model_id', 'deleted_at'], 'integer'],
            [['body'], 'string'],
            [['model_name', 'title', 'type'], 'string', 'max' => 255]
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
            'title' => Yii::t('models', 'Title'),
            'body' => Yii::t('models', 'Body'),
            'type' => Yii::t('models', 'Type'),
            'created_by' => Yii::t('models', 'Created By'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_by' => Yii::t('models', 'Updated By'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\NotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\NotificationQuery(get_called_class());
    }

}
