<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "signage_wayfinding".
 *
 * @property integer $id
 * @property string $batch
 * @property integer $quantity
 * @property string $sign_id
 * @property string $sign_code
 * @property string $level
 * @property string $message_side_1
 * @property string $message_side_2
 * @property string $fixing
 * @property string $notes
 */
class SignageWayfinding extends ActiveRecord
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
        return 'signage_wayfinding';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quantity'], 'integer'],
            [['message_side_1', 'message_side_2'], 'string'],
            [['batch', 'sign_id', 'sign_code', 'level', 'fixing', 'notes'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'batch' => Yii::t('models', 'Batch'),
            'quantity' => Yii::t('models', 'Quantity'),
            'sign_id' => Yii::t('models', 'Sign ID'),
            'sign_code' => Yii::t('models', 'Sign Code'),
            'level' => Yii::t('models', 'Level'),
            'message_side_1' => Yii::t('models', 'Message Side 1'),
            'message_side_2' => Yii::t('models', 'Message Side 2'),
            'fixing' => Yii::t('models', 'Fixing'),
            'notes' => Yii::t('models', 'Notes'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\SignageWayfindingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\SignageWayfindingQuery(get_called_class());
    }

}
