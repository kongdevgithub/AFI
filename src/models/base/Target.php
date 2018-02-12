<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "target".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $date
 * @property integer $target
 */
class Target extends ActiveRecord
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
        return 'target';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'date', 'target'],
            'create' => ['id', 'model_name', 'model_id', 'date', 'target'],
            'update' => ['id', 'model_name', 'model_id', 'date', 'target'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'date', 'target'], 'required'],
            [['model_id', 'target'], 'integer'],
            [['date'], 'safe'],
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
            'date' => Yii::t('models', 'Date'),
            'target' => Yii::t('models', 'Target'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\TargetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\TargetQuery(get_called_class());
    }

}
