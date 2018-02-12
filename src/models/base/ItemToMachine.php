<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "item_to_machine".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $machine_id
 * @property string $details
 * @property string $deleted_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \app\models\Machine $machine
 * @property \app\models\Item $item
 */
class ItemToMachine extends ActiveRecord
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
        return 'item_to_machine';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'item_id', 'machine_id', 'details', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'item_id', 'machine_id', 'details', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'item_id', 'machine_id', 'details', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'machine_id'], 'required'],
            [['item_id', 'machine_id'], 'integer'],
            [['details'], 'string'],
            [['deleted_at'], 'safe'],
            [['machine_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Machine::className(), 'targetAttribute' => ['machine_id' => 'id']],
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
            'machine_id' => Yii::t('models', 'Machine ID'),
            'details' => Yii::t('models', 'Details'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMachine()
    {
        return $this->hasOne(\app\models\Machine::className(), ['id' => 'machine_id']);
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
     * @return \app\models\query\ItemToMachineQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ItemToMachineQuery(get_called_class());
    }

}
