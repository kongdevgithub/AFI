<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;

/**
 * This is the model class for table "hub_spot".
 */
class HubSpot extends base\HubSpot
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hub_spot%}}';
    }

    /**
     * @param array $row
     * @return HubSpot|HubSpotCompany|HubSpotContact|HubSpotUser
     */
    public static function instantiate($row)
    {
        switch ($row['model_name']) {
            case HubSpotCompany::MODEL_NAME:
                return new HubSpotCompany();
            case HubSpotContact::MODEL_NAME:
                return new HubSpotContact();
            case HubSpotUser::MODEL_NAME:
                return new HubSpotUser();
            default:
                return new self;
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }

}
