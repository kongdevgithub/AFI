<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "unit".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $package_id
 * @property integer $quantity
 * @property string $status
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Item $item
 * @property \app\models\Package $package
 */
class Unit extends ActiveRecord
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
        return 'unit';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'item_id', 'package_id', 'quantity', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'item_id', 'package_id', 'quantity', 'status', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'item_id', 'package_id', 'quantity', 'status', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'quantity'], 'required'],
            [['item_id', 'package_id', 'quantity', 'deleted_at'], 'integer'],
            [['status'], 'string', 'max' => 255],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['package_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Package::className(), 'targetAttribute' => ['package_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'item_id' => Yii::t('models', 'Item ID'),
            'package_id' => Yii::t('models', 'Package ID'),
            'quantity' => Yii::t('models', 'Quantity'),
            'status' => Yii::t('models', 'Status'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(\app\models\Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(\app\models\Package::className(), ['id' => 'package_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\UnitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\UnitQuery(get_called_class());
    }

}
