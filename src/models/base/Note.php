<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "note".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property integer $important
 * @property string $title
 * @property string $body
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class Note extends ActiveRecord
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
        return 'note';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'important', 'title', 'body', 'sort_order', 'deleted_at', 'created_by', 'updated_by', 'created_at', 'updated_at'],
            'create' => ['id', 'model_name', 'model_id', 'important', 'title', 'body', 'sort_order', 'deleted_at', 'created_by', 'updated_by', 'created_at', 'updated_at'],
            'update' => ['id', 'model_name', 'model_id', 'important', 'title', 'body', 'sort_order', 'deleted_at', 'created_by', 'updated_by', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'important', 'title', 'body'], 'required'],
            [['model_id', 'important', 'sort_order', 'deleted_at'], 'integer'],
            [['body'], 'string'],
            [['model_name', 'title'], 'string', 'max' => 255]
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
            'important' => Yii::t('models', 'Important'),
            'title' => Yii::t('models', 'Title'),
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
     * @return \app\models\query\NoteQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\NoteQuery(get_called_class());
    }

}
