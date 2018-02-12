<?php

namespace app\modules\goldoc\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use Yii;

/**
 * This is the model class for table "supplier".
 *
 * @mixin LinkBehavior
 *
 * @property string $label
 */
class Supplier extends base\Supplier
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['code', 'name'], 'unique'];
        return $rules;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->code . ' - ' . $this->name;
    }
}
