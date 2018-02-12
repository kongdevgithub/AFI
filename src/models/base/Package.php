<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "package".
 *
 * @property integer $id
 * @property integer $package_type_id
 * @property integer $overflow_package_id
 * @property integer $pickup_id
 * @property integer $cartons
 * @property string $type
 * @property integer $width
 * @property integer $length
 * @property integer $height
 * @property integer $dead_weight
 * @property string $status
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Package $overflowPackage
 * @property \app\models\Package[] $packages
 * @property \app\models\Pickup $pickup
 * @property \app\models\PackageType $packageType
 * @property \app\models\Unit[] $units
 */
class Package extends ActiveRecord
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
        return 'package';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'package_type_id', 'overflow_package_id', 'pickup_id', 'cartons', 'type', 'width', 'length', 'height', 'dead_weight', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'package_type_id', 'overflow_package_id', 'pickup_id', 'cartons', 'type', 'width', 'length', 'height', 'dead_weight', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'package_type_id', 'overflow_package_id', 'pickup_id', 'cartons', 'type', 'width', 'length', 'height', 'dead_weight', 'status', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_type_id', 'overflow_package_id', 'pickup_id', 'cartons', 'width', 'length', 'height', 'dead_weight', 'deleted_at'], 'integer'],
            [['cartons'], 'required'],
            [['type'], 'string', 'max' => 11],
            [['status'], 'string', 'max' => 255],
            [['overflow_package_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Package::className(), 'targetAttribute' => ['overflow_package_id' => 'id']],
            [['pickup_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Pickup::className(), 'targetAttribute' => ['pickup_id' => 'id']],
            [['package_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\PackageType::className(), 'targetAttribute' => ['package_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'package_type_id' => Yii::t('models', 'Package Type ID'),
            'overflow_package_id' => Yii::t('models', 'Overflow Package ID'),
            'pickup_id' => Yii::t('models', 'Pickup ID'),
            'cartons' => Yii::t('models', 'Cartons'),
            'type' => Yii::t('models', 'Type'),
            'width' => Yii::t('models', 'Width'),
            'length' => Yii::t('models', 'Length'),
            'height' => Yii::t('models', 'Height'),
            'dead_weight' => Yii::t('models', 'Dead Weight'),
            'status' => Yii::t('models', 'Status'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOverflowPackage()
    {
        return $this->hasOne(\app\models\Package::className(), ['id' => 'overflow_package_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(\app\models\Package::className(), ['overflow_package_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPickup()
    {
        return $this->hasOne(\app\models\Pickup::className(), ['id' => 'pickup_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageType()
    {
        return $this->hasOne(\app\models\PackageType::className(), ['id' => 'package_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnits()
    {
        return $this->hasMany(\app\models\Unit::className(), ['package_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\PackageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PackageQuery(get_called_class());
    }

}
