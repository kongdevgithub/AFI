<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "correction".
 *
 * @property integer $id
 * @property string $action
 * @property string $model_name
 * @property integer $model_id
 * @property integer $user_id
 * @property string $reason
 * @property string $changes
 * @property integer $created_at
 * @property integer $updated_at
 */
class Correction extends ActiveRecord
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
        return 'correction';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'action', 'model_name', 'model_id', 'user_id', 'reason', 'changes', 'created_at', 'updated_at'],
            'create' => ['id', 'action', 'model_name', 'model_id', 'user_id', 'reason', 'changes', 'created_at', 'updated_at'],
            'update' => ['id', 'action', 'model_name', 'model_id', 'user_id', 'reason', 'changes', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'model_name', 'model_id', 'user_id', 'reason', 'changes'], 'required'],
            [['model_id', 'user_id'], 'integer'],
            [['changes'], 'string'],
            [['action', 'model_name', 'reason'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'action' => Yii::t('models', 'Action'),
            'model_name' => Yii::t('models', 'Model Name'),
            'model_id' => Yii::t('models', 'Model ID'),
            'user_id' => Yii::t('models', 'User ID'),
            'reason' => Yii::t('models', 'Reason'),
            'changes' => Yii::t('models', 'Changes'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\CorrectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CorrectionQuery(get_called_class());
    }

}
