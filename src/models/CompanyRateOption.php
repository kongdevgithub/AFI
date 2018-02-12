<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "company_rate_option".
 */
class CompanyRateOption extends base\CompanyRateOption
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['company_rate_id'] = Yii::t('app', 'Company Rate');
        $attributeLabels['option_id'] = Yii::t('app', 'Option');
        $attributeLabels['component_id'] = Yii::t('app', 'Component');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['option_id', 'component_id'], 'required'],
            [['option_id', 'component_id'], 'integer'],
            //[['company_rate_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyRate::className(), 'targetAttribute' => ['company_rate_id' => 'id']],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => Component::className(), 'targetAttribute' => ['component_id' => 'id']],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => Option::className(), 'targetAttribute' => ['option_id' => 'id']]
        ];
    }
}
