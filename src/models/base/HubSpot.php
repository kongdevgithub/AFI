<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "hub_spot".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $model_id
 * @property integer $hub_spot_id
 * @property integer $hub_spot_pushed
 * @property integer $hub_spot_pulled
 */
class HubSpot extends ActiveRecord
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
        return 'hub_spot';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'model_name', 'model_id', 'hub_spot_id', 'hub_spot_pushed', 'hub_spot_pulled'],
            'create' => ['id', 'model_name', 'model_id', 'hub_spot_id', 'hub_spot_pushed', 'hub_spot_pulled'],
            'update' => ['id', 'model_name', 'model_id', 'hub_spot_id', 'hub_spot_pushed', 'hub_spot_pulled'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'hub_spot_id', 'hub_spot_pushed', 'hub_spot_pulled'], 'integer'],
            [['hub_spot_id'], 'required'],
            [['model_name'], 'string', 'max' => 255]
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
            'hub_spot_id' => Yii::t('models', 'Hub Spot ID'),
            'hub_spot_pushed' => Yii::t('models', 'Hub Spot Pushed'),
            'hub_spot_pulled' => Yii::t('models', 'Hub Spot Pulled'),
        ];
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\HubSpotQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\HubSpotQuery(get_called_class());
    }

}
