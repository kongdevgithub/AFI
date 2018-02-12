<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "component_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $quote_class
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Component[] $components
 */
class ComponentType extends ActiveRecord
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
        return 'component_type';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'quote_class', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['deleted_at'], 'integer'],
            [['name', 'quote_class'], 'string', 'max' => 255]
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
            'quote_class' => Yii::t('models', 'Quote Class'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComponents()
    {
        return $this->hasMany(\app\models\Component::className(), ['component_type_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ComponentTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ComponentTypeQuery(get_called_class());
    }

}
