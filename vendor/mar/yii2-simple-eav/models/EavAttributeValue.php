<?php

namespace mar\eav\models;

use Yii;

/**
 * This is the model class for table "product_attribute_value".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $attribute_id
 * @property string $value
 */
class EavAttributeValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eav_attribute_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'attribute_id',], 'required'],
            [['object_id', 'attribute_id'], 'integer'],
            [['value'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'ID Объекта ',
            'attribute_id' => 'ID Атрибута',
            'value' => 'Value',
        ];
    }
}