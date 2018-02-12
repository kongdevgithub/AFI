<?php

namespace app\models\form;

use app\models\Option;
use app\models\Product;
use app\models\Item;
use app\models\ProductToOption;
use app\models\ProductToComponent;
use Yii;
use yii\base\Model;
use kartik\form\ActiveForm;

/**
 * Class ProductForm
 * @package app\models\form
 *
 * @property Product $product
 * @property Item[] $items
 * @property ProductToOption[] $productToOptions
 * @property ProductToComponent[] $productToComponents
 */
class ProductForm extends Model
{
    /**
     * @var string
     */
    public $correction_reason;

    /**
     * @var Product
     */
    private $_product;

    /**
     * @var Item[]
     */
    private $_items;

    /**
     * @var ProductToOption[]
     */
    private $_productToOptions;

    /**
     * @var ProductToComponent[]
     */
    private $_productToComponents;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Product'], 'required'],
            [['Items', 'ProductToOptions', 'ProductToComponents'], 'safe'],
            [['correction_reason'], 'required', 'when' => function ($model) {
                /** @var ProductForm $model */
                return $model->product->getChangedAlertEmails() ? true : false;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['ProductForm'])) {
            foreach ($values['ProductForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['ProductForm']);
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->product->validate()) {
            $error = true;
        }
        foreach ($this->items as $item) {
            if (!$item->validate()) {
                $error = true;
            }
        }
        foreach ($this->productToOptions as $productToOption) {
            if (!$productToOption->item_id || (isset($this->items[$productToOption->item_id]) && $this->items[$productToOption->item_id]->quantity > 0)) {
                if (!$productToOption->validate()) {
                    $error = true;
                }
            }
        }
        foreach ($this->productToComponents as $productToComponent) {
            if (!$productToComponent->item_id || (isset($this->items[$productToComponent->item_id]) || $this->items[$productToComponent->item_id]->quantity > 0)) {
                if (!$productToComponent->validate()) {
                    $error = true;
                }
            }
        }
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
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
        foreach ($this->product->forkQuantityProducts as $_product) {
            $_product->delete();
        }
        if (!$this->product->save()) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveItems()) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveProductToOptions()) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveProductToComponents()) {
            $transaction->rollBack();
            return false;
        }

        // update prebuild_days
        $this->product->job->prebuild_days = 0;
        foreach ($this->product->job->products as $_product) {
            if ($_product->prebuild_required) {
                $this->product->job->prebuild_days = 2;
                break;
            }
        }
        if ($this->product->job->isAttributeChanged('prebuild_days')) {
            $this->product->job->save(false);
        }

        $transaction->commit();

        $this->product->resetQuoteGenerated();
        $this->product->job->resetQuoteGenerated(false);
        return true;
    }

    /**
     * @return bool
     */
    public function saveItems()
    {
        $keep = [];
        foreach ($this->items as $k => $item) {
            $item->product_id = $this->product->id;
            if (!$item->save(false)) {
                return false;
            }
            $keep[] = $item->id;
            foreach ($this->productToOptions as $kk => $productToOption) {
                if ($productToOption->item_id == $k) {
                    $this->_productToOptions[$kk]->item_id = $item->id;
                }
            }
            foreach ($this->productToComponents as $kk => $productToComponent) {
                if ($productToComponent->item_id == $k) {
                    $this->_productToComponents[$kk]->item_id = $item->id;
                }
            }
        }
        $query = Item::find()->notDeleted()->andWhere(['product_id' => $this->product->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $item) {
            if (!$item->splits) {
                $item->delete();
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function saveProductToOptions()
    {
        $keep = [];
        foreach ($this->productToOptions as $productToOption) {
            $productToOption->product_id = $this->product->id;
            if (!$productToOption->save(false)) {
                return false;
            }
            $keep[] = $productToOption->id;
        }
        $query = ProductToOption::find()->andWhere(['product_id' => $this->product->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $productToOption) {
            $productToOption->delete();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function saveProductToComponents()
    {
        $keep = [];
        foreach ($this->productToComponents as $productToComponent) {
            $productToComponent->product_id = $this->product->id;
            if (!$productToComponent->save(false)) {
                return false;
            }
            $keep[] = $productToComponent->id;
        }
        $query = ProductToComponent::find()->andWhere(['product_id' => $this->product->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $productToComponent) {
            $productToComponent->delete();
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * @param $product
     */
    public function setProduct($product)
    {
        if ($product instanceof Product) {
            $this->_product = $product;
        } else if (is_array($product)) {
            $this->_product->setAttributes($product);
        }
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        if ($this->_items === null) {
            if ($this->product->isNewRecord) {
                $this->_items = [];
                if ($this->product->productType) {
                    foreach ($this->product->productType->productTypeToItemTypes as $productTypeToItemType) {
                        $item = new Item;
                        $item->quote_class = $productTypeToItemType->quote_class ? $productTypeToItemType->quote_class : $productTypeToItemType->itemType->quote_class;
                        $item->item_type_id = $productTypeToItemType->item_type_id;
                        $item->quantity = $productTypeToItemType->quantity;
                        $item->name = $productTypeToItemType->name;
                        $item->product_type_to_item_type_id = $productTypeToItemType->id;
                        $this->_items[] = $item;
                    }
                }
            } else {
                $this->_items = $this->product->items;
            }
        }
        return $this->_items;
    }

    /**
     * @param $id
     * @return Item|bool
     */
    private function getItem($id)
    {
        $item = $id ? Item::findOne($id) : false;
        if (!$item) {
            $item = new Item();
            $item->loadDefaultValues();
        }
        return $item;
    }

    /**
     * @param $items
     */
    public function setItems($items)
    {
        unset($items['__productItem_id__']); // remove the hidden "new Item" row
        $this->_items = [];
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
     * @return ProductToOption[]
     */
    public function getProductToOptions()
    {
        if ($this->_productToOptions === null) {
            if ($this->product->isNewRecord) {
                $this->_productToOptions = [];
                if ($this->product->productType) {
                    foreach ($this->product->productType->productTypeToOptions as $productTypeToOption) {
                        $productToOption = new ProductToOption;
                        $productToOption->option_id = $productTypeToOption->option_id;
                        $productToOption->loadDefaultValues();
                        $productToOption->product_type_to_option_id = $productTypeToOption->id;
                        $productToOption->quote_class = $productTypeToOption->quote_class;
                        $productToOption->quote_quantity_factor = $productTypeToOption->quantity_factor;
                        $this->_productToOptions[] = $productToOption;
                    }
                }
            } else {
                $this->_productToOptions = $this->product->productToOptions;
            }
        }
        return $this->_productToOptions;
    }

    /**
     * @param $id
     * @return ProductToOption|bool
     */
    private function getProductToOption($id)
    {
        $productToOption = $id ? ProductToOption::findOne($id) : false;
        if (!$productToOption) {
            $productToOption = new ProductToOption();
            $productToOption->loadDefaultValues();
        }
        return $productToOption;
    }

    /**
     * @param $productToOptions
     */
    public function setProductToOptions($productToOptions)
    {
        unset($productToOptions['__productToOption_id__']); // remove the hidden "new ProductToOption" row
        $this->_productToOptions = [];
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
     * @return ProductToComponent[]
     */
    public function getProductToComponents()
    {
        if ($this->_productToComponents === null) {
            if ($this->product->isNewRecord) {
                $this->_productToComponents = [];
                if ($this->product->productType) {
                    foreach ($this->product->productType->productTypeToComponents as $productTypeToComponent) {
                        $productToComponent = new ProductToComponent;
                        $productToComponent->loadDefaultValues();
                        $productToComponent->component_id = $productTypeToComponent->component_id;
                        $productToComponent->product_type_to_component_id = $productTypeToComponent->id;
                        $productToComponent->quantity = $productTypeToComponent->quantity;
                        $productToComponent->quote_class = $productTypeToComponent->quote_class;
                        $productToComponent->quote_quantity_factor = $productTypeToComponent->quantity_factor;
                        $this->_productToComponents[] = $productToComponent;
                    }
                }
            } else {
                $this->_productToComponents = $this->product->productToComponents;
            }
        }
        return $this->_productToComponents;
    }

    /**
     * @param $id
     * @return ProductToComponent|bool
     */
    private function getProductToComponent($id)
    {
        $productToComponent = $id ? ProductToComponent::findOne($id) : false;
        if (!$productToComponent) {
            $productToComponent = new ProductToComponent();
            $productToComponent->loadDefaultValues();
        }
        return $productToComponent;
    }

    /**
     * @param $productToComponents
     */
    public function setProductToComponents($productToComponents)
    {
        unset($productToComponents['__productToComponent_id__']); // remove the hidden "new ProductToComponent" row
        $this->_productToComponents = [];
        foreach ($productToComponents as $id => $productToComponent) {
            if (is_array($productToComponent)) {
                $this->_productToComponents[$id] = $this->getProductToComponent($id);
                $this->_productToComponents[$id]->setAttributes($productToComponent);
            } elseif ($productToComponent instanceof ProductToComponent) {
                $this->_productToComponents[$id] = $productToComponent;
            }
        }
    }

    /**
     * @param ActiveForm $form
     * @return mixed
     */
    public function errorSummary($form)
    {
        $errorLists = [];
        foreach ($this->getAllModels() as $id => $model) {
            $errorList = $form->errorSummary($model, [
                'header' => '<p>' . Yii::t('app', 'Please fix the following errors for') . ' <b>' . $id . '</b></p>',
            ]);
            $errorList = str_replace('<li></li>', '', $errorList); // remove the empty error
            $errorLists[] = $errorList;
        }
        return implode('', $errorLists);
    }

    /**
     * @return array
     */
    public function getAllModels()
    {
        $models = [
            'Product' => $this->product,
        ];
        foreach ($this->items as $id => $item) {
            $models['Item ' . $item->name] = $this->items[$id];
        }
        foreach ($this->productToOptions as $id => $productToOption) {
            $key = [];
            if ($productToOption->item_id) {
                if (isset($this->items[$productToOption->item_id])) {
                    $key[] = 'Item ' . $this->items[$productToOption->item_id]->name;
                }
            }
            if ($productToOption->option) {
                $key[] = 'Option ' . $productToOption->option->name;
            }
            $models[implode(' ', $key)] = $this->productToOptions[$id];
        }
        foreach ($this->productToComponents as $id => $productToComponent) {
            $key = [];
            if ($productToComponent->item_id) {
                if (isset($this->items[$productToComponent->item_id])) {
                    $key[] = 'Item ' . $this->items[$productToComponent->item_id]->name;
                }
            }
            if ($productToComponent->component) {
                $key[] = 'Component ' . $productToComponent->component->name;
            }
            $models[implode(' ', $key)] = $this->productToComponents[$id];
        }
        return $models;
    }
}