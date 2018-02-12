<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "signage_fa_to_venue".
 *
 * @property integer $id
 * @property integer $signage_fa_id
 * @property integer $venue_id
 * @property integer $quantity
 *
 * @property \app\modules\goldoc\models\Venue $venue
 * @property \app\modules\goldoc\models\SignageFa $signageFa
 */
class SignageFaToVenue extends ActiveRecord
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
        return 'signage_fa_to_venue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['signage_fa_id', 'venue_id', 'quantity'], 'required'],
            [['signage_fa_id', 'venue_id', 'quantity'], 'integer'],
            [['venue_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Venue::className(), 'targetAttribute' => ['venue_id' => 'id']],
            [['signage_fa_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\SignageFa::className(), 'targetAttribute' => ['signage_fa_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'signage_fa_id' => Yii::t('models', 'Signage Fa ID'),
            'venue_id' => Yii::t('models', 'Venue ID'),
            'quantity' => Yii::t('models', 'Quantity'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVenue()
    {
        return $this->hasOne(\app\modules\goldoc\models\Venue::className(), ['id' => 'venue_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSignageFa()
    {
        return $this->hasOne(\app\modules\goldoc\models\SignageFa::className(), ['id' => 'signage_fa_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\SignageFaToVenueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\SignageFaToVenueQuery(get_called_class());
    }

}
