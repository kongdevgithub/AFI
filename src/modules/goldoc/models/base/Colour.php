<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "colour".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 *
 * @property \app\modules\goldoc\models\Product[] $products
 */
class Colour extends ActiveRecord
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
        return 'colour';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
        return $this->hasMany(\app\modules\goldoc\models\Product::className(), ['colour_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\ColourQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\ColourQuery(get_called_class());
    }

}
