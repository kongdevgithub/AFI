<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "machine".
 *
 * @property integer $id
 * @property integer $machine_type_id
 * @property string $name
 * @property integer $deleted_at
 *
 * @property \app\models\ItemToMachine[] $itemToMachines
 * @property \app\models\MachineType $machineType
 */
class Machine extends ActiveRecord
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
        return 'machine';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'machine_type_id', 'name', 'deleted_at'],
            'create' => ['id', 'machine_type_id', 'name', 'deleted_at'],
            'update' => ['id', 'machine_type_id', 'name', 'deleted_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['machine_type_id', 'name'], 'required'],
            [['machine_type_id', 'deleted_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['machine_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\MachineType::className(), 'targetAttribute' => ['machine_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'machine_type_id' => Yii::t('models', 'Machine Type ID'),
            'name' => Yii::t('models', 'Name'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToMachines()
    {
        return $this->hasMany(\app\models\ItemToMachine::className(), ['machine_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMachineType()
    {
        return $this->hasOne(\app\models\MachineType::className(), ['id' => 'machine_type_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\MachineQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\MachineQuery(get_called_class());
    }

}
