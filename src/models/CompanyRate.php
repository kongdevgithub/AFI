<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;

/**
 * This is the model class for table "company_fixed_price".
 *
 * @mixin LinkBehavior
 */
class CompanyRate extends base\CompanyRate
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
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
        $attributeLabels['company_id'] = Yii::t('app', 'Company');
        $attributeLabels['product_type_id'] = Yii::t('app', 'Product Type');
        $attributeLabels['item_type_id'] = Yii::t('app', 'Item Type');
        $attributeLabels['option_id'] = Yii::t('app', 'Option');
        $attributeLabels['component_id'] = Yii::t('app', 'Component');
        return $attributeLabels;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyRateOptions()
    {
        return $this->hasMany(CompanyRateOption::className(), ['company_rate_id' => 'id'])
            ->andWhere(['deleted_at' => null]);
    }

    /**
     * @return string
     */
    public function getCompanyRateOptionsString()
    {
        $optionsString = [];
        foreach ($this->companyRateOptions as $companyRateOption) {
            $optionsString[] = $companyRateOption->option->name . '=' . $companyRateOption->component->code;
        }
        return implode(',', $optionsString);
    }

    /**
     * @return string
     */
    public function getCompanyRateOptionsHtml()
    {
        $optionsHtml = [];
        foreach ($this->companyRateOptions as $companyRateOption) {
            $option = '';
            if (Yii::$app->user->can('app_option_view', ['route' => true])) {
                $option .= Html::a($companyRateOption->option->name, ['//option/view', 'id' => $companyRateOption->option->id,]);
            } else {
                $option .= $companyRateOption->option->name;
            }
            $option .= ' = ';
            if (Yii::$app->user->can('app_component_view', ['route' => true])) {
                $option .= Html::a($companyRateOption->component->code . ' ' . $companyRateOption->component->name, ['//component/view', 'id' => $companyRateOption->component->id,]);
            } else {
                $option .= $companyRateOption->component->code . ' ' . $companyRateOption->component->name;
            }
            $optionsHtml[] = $option;
        }
        return implode('<br>', $optionsHtml);
    }
}
