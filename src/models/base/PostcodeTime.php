<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "postcode_time".
 *
 * @property integer $id
 * @property string $postcode
 * @property integer $lead_days
 */
class PostcodeTime extends ActiveRecord
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
        return 'postcode_time';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'postcode', 'lead_days'],
            'create' => ['id', 'postcode', 'lead_days'],
            'update' => ['id', 'postcode', 'lead_days'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['postcode', 'lead_days'], 'required'],
            [['lead_days'], 'integer'],
            [['postcode'], 'string', 'max' => 5]
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
            'lead_days' => Yii::t('models', 'Lead Days'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\PostcodeTimeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostcodeTimeQuery(get_called_class());
    }

}
