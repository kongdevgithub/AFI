<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use Yii;

/**
 * This is the model class for table "machine_type".
 *
 * @mixin LinkBehavior
 *
 */
class MachineType extends base\MachineType
{
    const MACHINE_TYPE_PRINTER = 1;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        //$behaviors[] = TimestampBehavior::className();
        //$behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }
}
