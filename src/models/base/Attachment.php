<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "attachment".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $filename
 * @property string $extension
 * @property string $filetype
 * @property integer $filesize
 * @property string $notes
 * @property integer $sort_order
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $updated_at
 */
class Attachment extends ActiveRecord
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
        return 'attachment';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'filename', 'extension', 'filetype', 'filesize', 'notes', 'sort_order', 'deleted_at', 'created_at', 'created_by', 'updated_by', 'updated_at'],
            'create' => ['id', 'model_name', 'model_id', 'filename', 'extension', 'filetype', 'filesize', 'notes', 'sort_order', 'deleted_at', 'created_at', 'created_by', 'updated_by', 'updated_at'],
            'update' => ['id', 'model_name', 'model_id', 'filename', 'extension', 'filetype', 'filesize', 'notes', 'sort_order', 'deleted_at', 'created_at', 'created_by', 'updated_by', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'filesize', 'sort_order', 'deleted_at'], 'integer'],
            [['notes'], 'string'],
            [['model_name', 'filename', 'extension', 'filetype'], 'string', 'max' => 255]
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
            'filename' => Yii::t('models', 'Filename'),
            'extension' => Yii::t('models', 'Extension'),
            'filetype' => Yii::t('models', 'Filetype'),
            'filesize' => Yii::t('models', 'Filesize'),
            'notes' => Yii::t('models', 'Notes'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'created_at' => Yii::t('models', 'Created At'),
            'created_by' => Yii::t('models', 'Created By'),
            'updated_by' => Yii::t('models', 'Updated By'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\AttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\AttachmentQuery(get_called_class());
    }

}
