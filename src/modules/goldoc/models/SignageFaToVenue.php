<?php

namespace app\modules\goldoc\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;

/**
 * This is the model class for table "signage_fa_to_venue".
 */
class SignageFaToVenue extends base\SignageFaToVenue
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
