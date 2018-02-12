<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "search".
 *
 * @property integer $id
 * @property string $name
 * @property integer $user_id
 * @property string $model_name
 * @property string $model_params
 * @property integer $created_at
 * @property integer $updated_at
 */
class Search extends ActiveRecord
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
        return 'search';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'user_id', 'model_name'], 'required'],
            [['user_id'], 'integer'],
            [['model_params'], 'string'],
            [['name', 'model_name'], 'string', 'max' => 255]
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
            'user_id' => Yii::t('models', 'User ID'),
            'model_name' => Yii::t('models', 'Model Name'),
            'model_params' => Yii::t('models', 'Model Params'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\SearchQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\SearchQuery(get_called_class());
    }

}
