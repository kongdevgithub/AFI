<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "export".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $model_name
 * @property string $model_params
 * @property string $status
 * @property integer $total_rows
 * @property string $remote_filename
 * @property integer $created_at
 * @property integer $updated_at
 */
class Export extends ActiveRecord
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
        return 'export';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'user_id', 'model_name', 'model_params', 'status', 'total_rows', 'remote_filename', 'created_at', 'updated_at'],
            'create' => ['id', 'user_id', 'model_name', 'model_params', 'status', 'total_rows', 'remote_filename', 'created_at', 'updated_at'],
            'update' => ['id', 'user_id', 'model_name', 'model_params', 'status', 'total_rows', 'remote_filename', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'model_name', 'status'], 'required'],
            [['user_id', 'total_rows'], 'integer'],
            [['model_params'], 'string'],
            [['model_name', 'status', 'remote_filename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'user_id' => Yii::t('models', 'User ID'),
            'model_name' => Yii::t('models', 'Model Name'),
            'model_params' => Yii::t('models', 'Model Params'),
            'status' => Yii::t('models', 'Status'),
            'total_rows' => Yii::t('models', 'Total Rows'),
            'remote_filename' => Yii::t('models', 'Remote Filename'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ExportQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ExportQuery(get_called_class());
    }

}
