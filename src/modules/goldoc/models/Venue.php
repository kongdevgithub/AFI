<?php

namespace app\modules\goldoc\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use Yii;

/**
 * This is the model class for table "venue".
 *
 * @mixin LinkBehavior
 *
 * @property string $label
 */
class Venue extends base\Venue
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

    /**
     * @return float|bool
     */
    public function getProductPriceSum()
    {
        return $this->find()
            ->select('SUM(product_price)')
            ->from('product')
            ->where(['venue_id' => $this->id])
            ->scalar();
    }

    /**
     * @return float|bool
     */
    public function getInstallPriceSum()
    {
        return $this->find()
            ->select('SUM(install_price)')
            ->from('product')
            ->where(['venue_id' => $this->id])
            ->scalar();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['venue_id' => 'id'])
            ->andWhere(['product.deleted_at' => null]);
    }

}
