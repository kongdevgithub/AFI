<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "machine_type".
 *
 * @property integer $id
 * @property integer $item_type_id
 * @property string $name
 *
 * @property \app\models\Machine[] $machines
 * @property \app\models\ItemType $itemType
 */
class MachineType extends ActiveRecord
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
        return 'machine_type';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'item_type_id', 'name'],
            'create' => ['id', 'item_type_id', 'name'],
            'update' => ['id', 'item_type_id', 'name'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_type_id', 'name'], 'required'],
            [['item_type_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ItemType::className(), 'targetAttribute' => ['item_type_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'item_type_id' => Yii::t('models', 'Item Type ID'),
            'name' => Yii::t('models', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMachines()
    {
        return $this->hasMany(\app\models\Machine::className(), ['machine_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemType()
    {
        return $this->hasOne(\app\models\ItemType::className(), ['id' => 'item_type_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\MachineTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\MachineTypeQuery(get_called_class());
    }

}
