<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "carrier".
 *
 * @property integer $id
 * @property string $name
 * @property integer $deleted_at
 * @property string $my_freight_code
 * @property string $cope_freight_code
 * @property string $tracking_url
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Pickup[] $pickups
 */
class Carrier extends ActiveRecord
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
        return 'carrier';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'deleted_at', 'my_freight_code', 'cope_freight_code', 'tracking_url', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'deleted_at', 'my_freight_code', 'cope_freight_code', 'tracking_url', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'deleted_at', 'my_freight_code', 'cope_freight_code', 'tracking_url', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'my_freight_code', 'cope_freight_code'], 'required'],
            [['deleted_at'], 'integer'],
            [['name', 'my_freight_code', 'cope_freight_code', 'tracking_url'], 'string', 'max' => 255]
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
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'my_freight_code' => Yii::t('models', 'My Freight Code'),
            'cope_freight_code' => Yii::t('models', 'Cope Freight Code'),
            'tracking_url' => Yii::t('models', 'Tracking Url'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPickups()
    {
        return $this->hasMany(\app\models\Pickup::className(), ['carrier_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\CarrierQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\CarrierQuery(get_called_class());
    }

}
