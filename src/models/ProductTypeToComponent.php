<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "product_type_to_component".
 *
 * @property float $quantity
 */
class ProductTypeToComponent extends base\ProductTypeToComponent
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['product_type_id'] = Yii::t('app', 'Product Type');
        $attributeLabels['product_type_to_item_type_id'] = Yii::t('app', 'Item');
        $attributeLabels['component_id'] = Yii::t('app', 'Component');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->quantity === null)) {
            $this->quantity = 1;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getQuoteClass()
    {
        if ($this->quote_class) {
            return $this->quote_class;
        }
        //if ($this->component->quote_class) {
        return $this->component->quote_class;
        //}
        //return $this->component->componentType->quote_class;
    }
}
