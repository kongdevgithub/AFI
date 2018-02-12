<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "dear".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $dear_id
 */
class Dear extends ActiveRecord
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
        return 'dear';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'dear_id'],
            'create' => ['id', 'model_name', 'model_id', 'dear_id'],
            'update' => ['id', 'model_name', 'model_id', 'dear_id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'dear_id'], 'required'],
            [['model_id'], 'integer'],
            [['model_name', 'dear_id'], 'string', 'max' => 255]
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
            'dear_id' => Yii::t('models', 'Dear ID'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\DearQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\DearQuery(get_called_class());
    }

}
