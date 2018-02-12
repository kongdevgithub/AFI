<?php

namespace app\models\form;

use app\components\fields\QuantityField;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\products\BaseProductQuote;
use app\models\Item;
use app\models\ItemType;
use app\models\Job;
use app\models\Option;
use app\models\Product;
use app\models\ProductToOption;
use app\models\ProductType;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class ProductBulkComponentsForm
 * @package app\models\form
 *
 * @property Product[] $products
 * @property Item[] $items
 * @property ProductToOption[] $productToOptions
 */
class ProductBulkComponentsForm extends Model
{
    /**
     * @var Job
     */
    public $job;

    /**
     * @var Product[]
     */
    private $_products;

    /**
     * @var Item[]
     */
    private $_items;

    /**
     * @var ProductToOption[]
     */
    private $_productToOptions;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Products', 'Items', 'ProductToOptions'], 'safe'],
        ];
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        $items = $this->items;
        $productToOptions = $this->productToOptions;
        $keep = [];

        foreach ($this->products as $id => $product) {
            $item = $items[$id];
            $productToOption = $productToOptions[$id];
            $component = (new QuantityField())->getComponent($productToOption);
            if (!$component) {
                $transaction->rollBack();
                throw new Exception('cannot find component ' . $productToOption->value);
            }

            // save product
            $product->job_id = $this->job->id;
            $product->product_type_id = ProductType::PRODUCT_TYPE_SPARE_PART;
            $product->quote_class = BaseProductQuote::className();
            $product->bulk_component = 1;
            $product->quote_hide_item_description = 1;
            $product->name = $component->code . ' ' . $component->name;
            if ($productToOption->quantity != 1) {
                $quantity = $productToOption->quantity * 1;
                $uom = $component->unit_of_measure;
                if ($uom == 'MT') {
                    $uom = 'MM';
                    $quantity *= 1000;
                }
                $product->name .= ' x' . $quantity . $uom;
            }
            if (!$product->save()) {
                $transaction->rollBack();
                throw new Exception('cannot save product-' . $product->id . ': ' . Helper::getErrorString($product));
            }
            $keep[] = $product->id;

            // save item
            $item->product_id = $product->id;
            $item->item_type_id = ItemType::ITEM_TYPE_FABRICATION;
            $item->quote_class = BaseItemQuote::className();
            $item->quantity = 1;
            $item->product_type_to_item_type_id = 331; // fabricated spare part
            $item->name = 'Spare Part';
            if (!$item->save()) {
                $transaction->rollBack();
                throw new Exception('cannot save item-' . $item->id . ': ' . Helper::getErrorString($item));
            }

            // save productToOption
            $productToOption->product_id = $product->id;
            $productToOption->item_id = $item->id;
            $productToOption->product_type_to_option_id = 771; // spare part
            $productToOption->option_id = Option::OPTION_SPARE_PART;
            $productToOption->quote_class = BaseComponentQuote::className();
            if (!$productToOption->save()) {
                $transaction->rollBack();
                throw new Exception('cannot save productToOption-' . $productToOption->id . ': ' . Helper::getErrorString($productToOption));
            }
        }

        // remove old products
        $query = Product::find()->andWhere(['job_id' => $this->job->id, 'bulk_component' => 1]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $_product) {
            $_product->delete();
        }

        $transaction->commit();

        $this->job->resetQuoteGenerated();

        return true;
    }

    /**
     * @return Product[]|null
     */
    public function getProducts()
    {
        if ($this->_products === null) {
            $this->_products = [];
            foreach ($this->job->products as $product) {
                if (!$product->bulk_component) continue;
                $this->_products[$product->id] = $product;
                $this->_items[$product->id] = $product->items[0];
                $this->_productToOptions[$product->id] = $product->items[0]->productToOptions[0];
            }
        }
        return $this->_products;
    }

    /**
     * @param array[] $products
     */
    public function setProducts($products)
    {
        unset($products['__id__']); // remove the hidden "new Product" row
        foreach ($products as $id => $product) {
            if (is_array($product)) {
                $this->_products[$id] = $this->getProduct($id);
                $this->_products[$id]->setAttributes($product);

            } elseif ($product instanceof Product) {
                $this->_products[$id] = $product;
            }
        }
    }

    /**
     * @param array[] $items
     */
    public function setItems($items)
    {
        foreach ($items as $id => $item) {
            if (is_array($item)) {
                $this->_items[$id] = $this->getItem($id);
                $this->_items[$id]->setAttributes($item);

            } elseif ($item instanceof Item) {
                $this->_items[$id] = $item;
            }
        }
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param array[] $productToOptions
     */
    public function setProductToOptions($productToOptions)
    {
        foreach ($productToOptions as $id => $productToOption) {
            if (is_array($productToOption)) {
                $this->_productToOptions[$id] = $this->getProductToOption($id);
                $this->_productToOptions[$id]->setAttributes($productToOption);

            } elseif ($productToOption instanceof ProductToOption) {
                $this->_productToOptions[$id] = $productToOption;
            }
        }
    }

    /**
     * @return ProductToOption[]
     */
    public function getProductToOptions()
    {
        return $this->_productToOptions;
    }

    /**
     * @param $id
     * @return Product|bool
     */
    private function getProduct($id)
    {
        $product = $id ? Product::findOne($id) : false;
        if (!$product) {
            $product = new Product();
            $product->loadDefaultValues();
        }
        return $product;
    }

    /**
     * @param $product_id
     * @return Item
     */
    private function getItem($product_id)
    {
        $item = $product_id ? Item::findOne(['product_id' => $product_id]) : false;
        if (!$item) {
            $item = new Item();
            $item->loadDefaultValues();
        }
        return $item;
    }

    /**
     * @param $product_id
     * @return ProductToOption
     */
    private function getProductToOption($product_id)
    {
        $productToOption = $product_id ? ProductToOption::findOne(['product_id' => $product_id]) : false;
        if (!$productToOption) {
            $productToOption = new ProductToOption();
            $productToOption->loadDefaultValues();
        }
        return $productToOption;
    }

}