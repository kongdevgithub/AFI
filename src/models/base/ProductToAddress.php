<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_to_address".
 *
 * @property integer $id
 * @property integer $address_id
 * @property integer $product_id
 * @property integer $quantity
 *
 * @property \app\models\Product $product
 * @property \app\models\Address $address
 */
class ProductToAddress extends ActiveRecord
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
        return 'product_to_address';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'address_id', 'product_id', 'quantity'],
            'create' => ['id', 'address_id', 'product_id', 'quantity'],
            'update' => ['id', 'address_id', 'product_id', 'quantity'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address_id', 'product_id', 'quantity'], 'required'],
            [['address_id', 'product_id', 'quantity'], 'integer'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => Yii::t('models', 'Product ID'),
            'quantity' => Yii::t('models', 'Quantity'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(\app\models\Product::className(), ['id' => 'product_id']);
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
     * @return \app\models\query\ProductToAddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductToAddressQuery(get_called_class());
    }

}
