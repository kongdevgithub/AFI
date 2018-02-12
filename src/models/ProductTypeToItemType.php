<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "product_type_to_item_type".
 */
class ProductTypeToItemType extends base\ProductTypeToItemType
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
        $attributeLabels['item_type_id'] = Yii::t('app', 'Item Type');
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
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(ProductTypeToComponent::className(), ['product_type_to_item_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(ProductTypeToOption::className(), ['product_type_to_item_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->productTypeToOptions as $productTypeToOption) {
            $productTypeToOption->delete();
        }
        foreach ($this->productTypeToComponents as $productTypeToComponent) {
            $productTypeToComponent->delete();
        }
        return parent::beforeDelete();
    }

}
