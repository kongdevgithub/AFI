<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "signage_fa".
 *
 * @property integer $id
 * @property string $code
 * @property string $comment
 * @property string $sign_text
 * @property string $goldoc_product_allocated
 * @property string $material
 * @property string $width
 * @property string $height
 * @property string $fixing
 *
 * @property \app\modules\goldoc\models\SignageFaToVenue[] $signageFaToVenues
 */
class SignageFa extends ActiveRecord
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
        return 'signage_fa';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sign_text'], 'string'],
            [['width', 'height'], 'number'],
            [['code'], 'string', 'max' => 16],
            [['comment', 'goldoc_product_allocated', 'material', 'fixing'], 'string', 'max' => 255]
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
            'comment' => Yii::t('models', 'Comment'),
            'sign_text' => Yii::t('models', 'Sign Text'),
            'goldoc_product_allocated' => Yii::t('models', 'Goldoc Product Allocated'),
            'material' => Yii::t('models', 'Material'),
            'width' => Yii::t('models', 'Width'),
            'height' => Yii::t('models', 'Height'),
            'fixing' => Yii::t('models', 'Fixing'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSignageFaToVenues()
    {
        return $this->hasMany(\app\modules\goldoc\models\SignageFaToVenue::className(), ['signage_fa_id' => 'id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\SignageFaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\SignageFaQuery(get_called_class());
    }

}
