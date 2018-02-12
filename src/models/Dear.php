<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;

/**
 * This is the model class for table "dear".
 */
class Dear extends base\Dear
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dear%}}';
    }

    /**
     * @param array $row
     * @return Dear|DearProduct
     */
    public static function instantiate($row)
    {
        switch ($row['model_name']) {
            case DearProduct::MODEL_NAME:
                return new DearProduct();
            case DearSale::MODEL_NAME:
                return new DearSale();
            default:
                return new self;
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }
}
