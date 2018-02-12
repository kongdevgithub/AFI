<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "machine".
 *
 * @mixin LinkBehavior
 *
 */
class Machine extends base\Machine
{

    const MACHINE_MTEX = 6;
    const MACHINE_MTEX_HS = 7;
    const MACHINE_MTEX_HS_2 = 9;
    const MACHINE_MTEX_HS_3 = 11;
    const MACHINE_DURST = 3;
    const MACHINE_EVO = 4;
    const MACHINE_VUTEK = 2;
    const MACHINE_COLOR_PAINTER = 5;
    const MACHINE_SWISS_Q = 8;
    //should be 10 but in Trello was asked to name is 7
    const MACHINE_SEVEN = 10;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        //$behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }
}
