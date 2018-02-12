<?php

namespace app\modules\goldoc\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use Yii;

/**
 * This is the model class for table "signage_wayfinding".
 *
 * @mixin LinkBehavior
 */
class SignageWayfinding extends base\SignageWayfinding
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => LinkBehavior::className(),
            'moduleName' => 'goldoc',
        ];
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }

}
