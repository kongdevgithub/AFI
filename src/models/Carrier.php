<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "carrier".
 *
 * @mixin LinkBehavior
 */
class Carrier extends base\Carrier
{
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

    /**
     * The name of this model to be used in titles
     *
     * @return string
     */
    public function getLinkText()
    {
        return $this->name;
    }

}
