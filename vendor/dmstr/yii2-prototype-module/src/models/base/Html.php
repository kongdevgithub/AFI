<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace dmstr\modules\prototype\models\base;

use Yii;

/**
 * This is the base-model class for table "app_html".
 *
 * @property integer $id
 * @property string $key
 * @property string $value
 */
abstract class Html extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%html}}';
    }

    /**
     * @inheritdoc
     * @return \dmstr\modules\prototype\models\query\HtmlQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \dmstr\modules\prototype\models\query\HtmlQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255],
            [['key'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('prototype', 'ID'),
            'key' => Yii::t('prototype', 'Key'),
            'value' => Yii::t('prototype', 'Value'),
        ];
    }


}
