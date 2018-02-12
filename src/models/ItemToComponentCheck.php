<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;

/**
 * This is the model class for table "item_to_component_check".
 */
class ItemToComponentCheck extends base\ItemToComponentCheck
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['item'],
        ];
        return $behaviors;
    }
}
