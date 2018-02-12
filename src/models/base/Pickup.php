<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "pickup".
 *
 * @property integer $id
 * @property integer $carrier_id
 * @property string $carrier_ref
 * @property string $status
 * @property string $pod_date
 * @property integer $deleted_at
 * @property integer $complete_at
 * @property integer $collected_at
 * @property integer $emailed_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Package[] $packages
 * @property \app\models\Carrier $carrier
 */
class Pickup extends ActiveRecord
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
        return 'pickup';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'carrier_id', 'carrier_ref', 'status', 'pod_date', 'deleted_at', 'complete_at', 'collected_at', 'emailed_at', 'created_at', 'updated_at'],
            'create' => ['id', 'carrier_id', 'carrier_ref', 'status', 'pod_date', 'deleted_at', 'complete_at', 'collected_at', 'emailed_at', 'created_at', 'updated_at'],
            'update' => ['id', 'carrier_id', 'carrier_ref', 'status', 'pod_date', 'deleted_at', 'complete_at', 'collected_at', 'emailed_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['carrier_id', 'deleted_at', 'complete_at', 'collected_at', 'emailed_at'], 'integer'],
            [['pod_date'], 'safe'],
            [['carrier_ref', 'status'], 'string', 'max' => 255],
            [['carrier_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Carrier::className(), 'targetAttribute' => ['carrier_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'carrier_id' => Yii::t('models', 'Carrier ID'),
            'carrier_ref' => Yii::t('models', 'Carrier Ref'),
            'status' => Yii::t('models', 'Status'),
            'pod_date' => Yii::t('models', 'Pod Date'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'complete_at' => Yii::t('models', 'Complete At'),
            'collected_at' => Yii::t('models', 'Collected At'),
            'emailed_at' => Yii::t('models', 'Emailed At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(\app\models\Package::className(), ['pickup_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCarrier()
    {
        return $this->hasOne(\app\models\Carrier::className(), ['id' => 'carrier_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\PickupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PickupQuery(get_called_class());
    }

}
