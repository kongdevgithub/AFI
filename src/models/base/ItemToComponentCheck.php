<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "item_to_component_check".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $component_id
 * @property integer $quantity
 *
 * @property \app\models\Component $component
 * @property \app\models\Item $item
 */
class ItemToComponentCheck extends ActiveRecord
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
        return 'item_to_component_check';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'component_id', 'quantity'], 'required'],
            [['item_id', 'component_id', 'quantity'], 'integer'],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Component::className(), 'targetAttribute' => ['component_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']]
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
            'component_id' => Yii::t('models', 'Component ID'),
            'quantity' => Yii::t('models', 'Quantity'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComponent()
    {
        return $this->hasOne(\app\models\Component::className(), ['id' => 'component_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(\app\models\Item::className(), ['id' => 'item_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ItemToComponentCheckQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ItemToComponentCheckQuery(get_called_class());
    }

}
