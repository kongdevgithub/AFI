<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "account_terms".
 *
 * @mixin LinkBehavior
 */
class AccountTerm extends base\AccountTerm
{
    /**
     *
     */
    const ACCOUNT_TERM_DEFAULT = 2;
    const ACCOUNT_TERM_30DAY = 1;
    const ACCOUNT_TERM_COD = 2;
    const ACCOUNT_TERM_PWO = 3;

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
