<?php

namespace app\modules\goldoc\models\form;

use app\modules\goldoc\models\Product;
use Yii;
use yii\base\Model;

/**
 * Class BulkProductStatusForm
 * @package app\models\form
 *
 */
class BulkProductStatusForm extends Model
{

    /**
     * @var int[]
     */
    public $ids;

    /**
     * @var string
     */
    public $old_status;

    /**
     * @var string
     */
    public $new_status;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['new_status'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['BulkProductStatusForm'])) {
            foreach ($values['BulkProductStatusForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['BulkProductStatusForm']);
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (!$this->validateIds()) {
            $this->addError('ids', Yii::t('app', 'Cannot handle mixed statuses.'));
        }
        return parent::beforeValidate();
    }

    /**
     * @return bool
     */
    public function validateIds()
    {
        $status = null;
        foreach ($this->ids as $id) {
            $product = Product::findOne($id);
            if ($status == null) {
                $status = $product->status;
            }
            if ($product->status != $status) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        foreach ($this->getProducts() as $product) {
            if (!$this->processProduct($product)) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        $products = [];
        foreach ($this->ids as $id) {
            $product = Product::findOne($id);
            if ($product) {
                $products[] = $product;
            }
        }
        return $products;
    }

    /**
     * @param Product $product
     * @return bool
     */
    protected function processProduct($product)
    {
        // save Product
        $product->status = $this->new_status;
        if (!$product->save(false)) {
            return false;
        }
        return true;
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatus($status)
    {
        if ($status) {
            return $status;
        }
        foreach ($this->ids as $id) {
            $product = Product::findOne($id);
            if ($product) {
                return $product->status;
            }
        }
        return 'goldoc-product/draft';
    }
}