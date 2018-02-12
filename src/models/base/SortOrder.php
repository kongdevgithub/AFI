<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "sort_order".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property integer $sort_order
 */
class SortOrder extends ActiveRecord
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
        return 'sort_order';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'sort_order'],
            'create' => ['id', 'model_name', 'model_id', 'sort_order'],
            'update' => ['id', 'model_name', 'model_id', 'sort_order'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'sort_order'], 'integer'],
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
            'sort_order' => Yii::t('models', 'Sort Order'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\SortOrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\SortOrderQuery(get_called_class());
    }

}
