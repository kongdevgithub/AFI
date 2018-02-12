<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "size".
 *
 * @mixin LinkBehavior
 */
class Size extends base\Size
{

    /**
     *
     */
    const SIZE_DS_CURVE_TOE_IN_OFFSET = 53;
    /**
     *
     */
    const SIZE_DS_CURVE_TOE_OUT_OFFSET = 54;
    /**
     *
     */
    const SIZE_SS_CURVE_TOE_IN_OFFSET = 55;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }
}
