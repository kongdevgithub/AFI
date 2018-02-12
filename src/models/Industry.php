<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "industry".
 */
class Industry extends base\Industry
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @return array
     */
    public static function marketPhaseOpts()
    {
        return [
            '0' => Yii::t('app', 'Mature'),
            '1' => Yii::t('app', 'Growing'),
            '2' => Yii::t('app', 'Emerging'),
        ];
    }

}
