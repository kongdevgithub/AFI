<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "link".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $title
 * @property string $url
 * @property string $body
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class Link extends ActiveRecord
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
        return 'link';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'title', 'url'], 'required'],
            [['model_id', 'sort_order', 'deleted_at'], 'integer'],
            [['body'], 'string'],
            [['model_name', 'title'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 1024]
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
            'url' => Yii::t('models', 'Url'),
            'body' => Yii::t('models', 'Body'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'created_by' => Yii::t('models', 'Created By'),
            'updated_by' => Yii::t('models', 'Updated By'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\LinkQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\LinkQuery(get_called_class());
    }

}
