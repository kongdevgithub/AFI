<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "package_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property integer $width
 * @property integer $length
 * @property integer $height
 * @property integer $dead_weight
 * @property integer $deleted_at
 *
 * @property \app\models\Package[] $packages
 */
class PackageType extends ActiveRecord
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
        return 'package_type';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'type', 'width', 'length', 'height', 'dead_weight', 'deleted_at'],
            'create' => ['id', 'name', 'type', 'width', 'length', 'height', 'dead_weight', 'deleted_at'],
            'update' => ['id', 'name', 'type', 'width', 'length', 'height', 'dead_weight', 'deleted_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['width', 'length', 'height', 'dead_weight', 'deleted_at'], 'integer'],
            [['name', 'type'], 'string', 'max' => 255]
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
            'type' => Yii::t('models', 'Type'),
            'width' => Yii::t('models', 'Width'),
            'length' => Yii::t('models', 'Length'),
            'height' => Yii::t('models', 'Height'),
            'dead_weight' => Yii::t('models', 'Dead Weight'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(\app\models\Package::className(), ['package_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\PackageTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PackageTypeQuery(get_called_class());
    }

}
