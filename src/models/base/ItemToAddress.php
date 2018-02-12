<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "item_to_address".
 *
 * @property integer $id
 * @property integer $address_id
 * @property integer $item_id
 * @property integer $quantity
 *
 * @property \app\models\Item $item
 * @property \app\models\Address $address
 */
class ItemToAddress extends ActiveRecord
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
        return 'item_to_address';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'address_id', 'item_id', 'quantity'],
            'create' => ['id', 'address_id', 'item_id', 'quantity'],
            'update' => ['id', 'address_id', 'item_id', 'quantity'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address_id', 'item_id', 'quantity'], 'required'],
            [['address_id', 'item_id', 'quantity'], 'integer'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Address::className(), 'targetAttribute' => ['address_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'address_id' => Yii::t('models', 'Address ID'),
            'item_id' => Yii::t('models', 'Item ID'),
            'quantity' => Yii::t('models', 'Quantity'),
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
    public function getAddress()
    {
        return $this->hasOne(\app\models\Address::className(), ['id' => 'address_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ItemToAddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ItemToAddressQuery(get_called_class());
    }

}
