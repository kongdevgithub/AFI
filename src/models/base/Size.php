<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "size".
 *
 * @property integer $id
 * @property string $name
 * @property string $label
 * @property integer $width
 * @property integer $height
 * @property integer $depth
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 */
class Size extends ActiveRecord
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
        return 'size';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'label', 'width', 'height', 'depth', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'label', 'width', 'height', 'depth', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'label', 'width', 'height', 'depth', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['width', 'height', 'depth', 'deleted_at'], 'integer'],
            [['name', 'label'], 'string', 'max' => 255]
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
            'label' => Yii::t('models', 'Label'),
            'width' => Yii::t('models', 'Width'),
            'height' => Yii::t('models', 'Height'),
            'depth' => Yii::t('models', 'Depth'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\SizeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\SizeQuery(get_called_class());
    }

}
