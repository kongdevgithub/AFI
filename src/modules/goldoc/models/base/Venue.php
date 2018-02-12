<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "venue".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 *
 * @property \app\modules\goldoc\models\Product[] $products
 * @property \app\modules\goldoc\models\SignageFaToVenue[] $signageFaToVenues
 */
class Venue extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbGoldoc;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'venue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 16],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'code' => Yii::t('models', 'Code'),
            'name' => Yii::t('models', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\modules\goldoc\models\Product::className(), ['venue_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSignageFaToVenues()
    {
        return $this->hasMany(\app\modules\goldoc\models\SignageFaToVenue::className(), ['venue_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\VenueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\VenueQuery(get_called_class());
    }

}
