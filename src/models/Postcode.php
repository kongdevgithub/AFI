<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;

/**
 * This is the model class for table "postcode".
 *
 * @link http://download.geonames.org/export/zip/
 */
class Postcode extends base\Postcode
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
