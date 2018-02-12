<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use mar\eav\behaviors\EavBehavior;
use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property User $user
 * @property string $phone
 */
class Profile extends \dektrium\user\models\Profile
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['phone'], 'string'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['phone'] = Yii::t('app', 'Phone');
        return $attributeLabels;
    }

}
