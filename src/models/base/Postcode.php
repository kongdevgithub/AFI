<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "postcode".
 *
 * @property integer $id
 * @property string $postcode
 * @property string $city
 * @property string $state
 * @property string $country
 */
class Postcode extends ActiveRecord
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
        return 'postcode';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'postcode', 'city', 'state', 'country'],
            'create' => ['id', 'postcode', 'city', 'state', 'country'],
            'update' => ['id', 'postcode', 'city', 'state', 'country'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['postcode', 'city', 'state', 'country'], 'required'],
            [['postcode', 'state'], 'string', 'max' => 5],
            [['city', 'country'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'postcode' => Yii::t('models', 'Postcode'),
            'city' => Yii::t('models', 'City'),
            'state' => Yii::t('models', 'State'),
            'country' => Yii::t('models', 'Country'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\PostcodeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostcodeQuery(get_called_class());
    }

}
