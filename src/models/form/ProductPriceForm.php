<?php

namespace app\models\form;

use app\components\quotes\products\BaseProductQuote;
use app\models\ItemType;
use app\models\Product;
use Yii;
use yii\base\Model;

/**
 * Class ProductPriceForm
 * @package app\models\form
 *
 * @property Product $product
 */
class ProductPriceForm extends Model
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var
     */
    public $factor;

    /**
     * @var
     */
    public $factor_price;

    /**
     * @var
     */
    public $retail_price;

    /**
     * @var
     */
    public $area_price;

    /**
     * @var
     */
    public $perimeter_price;

    /**
     * @var
     */
    public $preserve_unit_prices;

    /**
     * @var
     */
    public $prevent_rate_prices;

    /**
     * @var bool
     */
    public $zero_factor = false;
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['factor'], 'required'],
            [['factor'], 'number', 'min' => 1, 'when' => function ($model) {
                return !Yii::$app->user->can('_reduce_product_factor');
            }],
            [['factor_price', 'retail_price', 'preserve_unit_prices', 'prevent_rate_prices'], 'safe'],
        ];
    }
    public function attributeLabels()
    {
        $parentLabels =  parent::attributeLabels();
        $parentLabels['zero_factor'] = "Zero Price";
        return $parentLabels;
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->product->quote_class = BaseProductQuote::className();
        $this->product->quote_factor = $this->factor;
        $this->product->preserve_unit_prices = $this->preserve_unit_prices;
        $this->product->prevent_rate_prices = $this->prevent_rate_prices;

        $transaction = Yii::$app->dbData->beginTransaction();
        if (!$this->product->save()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();

        $this->product->resetQuoteGenerated();
        $this->product->job->resetQuoteGenerated(false);

        return true;
    }

    /**
     * @return int
     */
    public function getArea()
    {
        $area = 0;
        foreach ($this->product->items as $item) {
            if (!$item->quantity || $item->item_type_id != ItemType::ITEM_TYPE_PRINT) {
                continue;
            }
            $size = $item->getSize();
            if (!$size || empty($size['width'] || empty($size['height']))) {
                continue;
            }
            $area += $this->product->quantity * $item->quantity * $size['width'] * $size['height'] / 1000 / 1000;
        }
        return round($area, 4);
    }

    /**
     * @return int
     */
    public function getPerimeter()
    {
        $perimeter = 0;
        foreach ($this->product->items as $item) {
            if (!$item->quantity || $item->item_type_id != ItemType::ITEM_TYPE_FABRICATION) {
                continue;
            }
            $size = $item->getSize();
            if (!$size || empty($size['width']) || empty($size['height'])) {
                continue;
            }
            $length = ($size['width'] + $size['height']) * 2;
            if (!empty($size['depth'])) {
                $length = ($size['width'] + $size['height'] + $size['depth']) * 4;
            }
            $perimeter += $this->product->quantity * $item->quantity * $length / 1000;
        }
        return round($perimeter, 4);
    }

}