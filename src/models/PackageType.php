<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;

/**
 * This is the model class for table "package_type".
 */
class PackageType extends base\PackageType
{


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //$behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        //$behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }


    /**
     * @return string
     */
    public function getLabel()
    {
        $label = [];
        $label[] = $this->name;
        $label[] = $this->type;
        if ($this->width || $this->length || $this->height) {
            $size = [];
            if ($this->width) {
                $size[] = $this->width;
            }
            if ($this->length) {
                $size[] = $this->length;
            }
            if ($this->height) {
                $size[] = $this->height;
            }
            $label[] = '(' . implode('x', $size) . ')';
        }
        $label[] = $this->dead_weight;
        return implode(' ', $label);
    }


}
