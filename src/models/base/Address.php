<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "address".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property string $type
 * @property string $name
 * @property string $street
 * @property string $postcode
 * @property string $city
 * @property string $state
 * @property string $country
 * @property integer $deleted_at
 * @property string $contact
 * @property string $phone
 * @property string $instructions
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\ItemToAddress[] $itemToAddresses
 * @property \app\models\ProductToAddress[] $productToAddresses
 */
class Address extends ActiveRecord
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
        return 'address';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'type', 'name', 'street', 'postcode', 'city', 'state', 'country', 'deleted_at', 'contact', 'phone', 'instructions', 'created_at', 'updated_at'],
            'create' => ['id', 'model_name', 'model_id', 'type', 'name', 'street', 'postcode', 'city', 'state', 'country', 'deleted_at', 'contact', 'phone', 'instructions', 'created_at', 'updated_at'],
            'update' => ['id', 'model_name', 'model_id', 'type', 'name', 'street', 'postcode', 'city', 'state', 'country', 'deleted_at', 'contact', 'phone', 'instructions', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'type', 'name', 'street', 'postcode', 'city', 'state', 'country'], 'required'],
            [['model_id', 'deleted_at'], 'integer'],
            [['model_name'], 'string', 'max' => 32],
            [['type', 'name', 'street', 'postcode', 'city', 'state', 'country', 'contact', 'phone', 'instructions'], 'string', 'max' => 255]
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
            'type' => Yii::t('models', 'Type'),
            'name' => Yii::t('models', 'Name'),
            'street' => Yii::t('models', 'Street'),
            'postcode' => Yii::t('models', 'Postcode'),
            'city' => Yii::t('models', 'City'),
            'state' => Yii::t('models', 'State'),
            'country' => Yii::t('models', 'Country'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'contact' => Yii::t('models', 'Contact'),
            'phone' => Yii::t('models', 'Phone'),
            'instructions' => Yii::t('models', 'Instructions'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToAddresses()
    {
        return $this->hasMany(\app\models\ItemToAddress::className(), ['address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToAddresses()
    {
        return $this->hasMany(\app\models\ProductToAddress::className(), ['address_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\AddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\AddressQuery(get_called_class());
    }

}
