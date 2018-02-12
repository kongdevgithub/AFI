<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "component_type".
 *
 * @mixin LinkBehavior
 *
 */
class ComponentType extends base\ComponentType
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
     * @return \yii\db\ActiveQuery
     */
    public function getComponents()
    {
        return $this->hasMany(Component::className(), ['component_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('componentType');
    }

}
