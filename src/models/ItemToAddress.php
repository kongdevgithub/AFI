<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;

/**
 * This is the model class for table "item_to_address".
 */
class ItemToAddress extends base\ItemToAddress
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
}
