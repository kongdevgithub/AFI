<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "search".
 */
class Search extends base\Search
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors[] = TimestampBehavior::className();
        //$behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

}
