<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;

/**
 * This is the model class for table "target".
 */
class Target extends base\Target
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
