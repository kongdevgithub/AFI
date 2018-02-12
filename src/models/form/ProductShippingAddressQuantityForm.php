<?php
namespace app\models\form;

use app\models\Address;
use app\models\Item;
use app\models\Product;
use app\models\ProductToAddress;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Model;

/**
 * ProductShippingAddressQuantityForm
 *
 * @property \app\models\ProductToAddress[] $productToAddresses
 */
class ProductShippingAddressQuantityForm extends Model
{

    /**
     * @var Product
     */
    public $product;

    /**
     * @var array
     */
    public $quantity;

    /**
     * @var ProductToAddress[]
     */
    private $_productToAddresses;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['ProductToAddresses'], 'required'],
        ];
    }

    public function afterValidate()
    {
        $error = false;
        foreach ($this->productToAddresses as $productToAddress) {
            if (!$productToAddress->validate()) {
                $error = true;
            }
        }
        if ($error) {
            $this->addError(null);
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
        if (!$this->saveProductToAddresses()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    /**
     * @return bool
     */
    public function saveProductToAddresses()
    {
        $keep = [];
        foreach ($this->productToAddresses as $productToAddress) {
            $productToAddress->product_id = $this->product->id;
            if (!$productToAddress->save(false)) {
                return false;
            }
            $keep[] = $productToAddress->id;
        }
        $query = ProductToAddress::find()->andWhere(['product_id' => $this->product->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $item) {
            $item->delete();
        }
        return true;
    }

    /**
     * @return ProductToAddress[]
     */
    public function getProductToAddresses()
    {
        if ($this->_productToAddresses === null) {
            $this->_productToAddresses = [];
            foreach ($this->product->job->addresses as $address) {
                if ($address->type != Address::TYPE_SHIPPING) continue;
                $this->_productToAddresses[] = $this->getProductToAddress($address->id);
            }

        }
        return $this->_productToAddresses;
    }

    /**
     * @param $id
     * @return ProductToAddress|bool
     */
    private function getProductToAddress($id)
    {
        $productToAddress = $id ? ProductToAddress::findOne(['address_id' => $id, 'product_id' => $this->product->id]) : false;
        if (!$productToAddress) {
            $productToAddress = new ProductToAddress();
            $productToAddress->loadDefaultValues();
            $productToAddress->address_id = $id;
            $productToAddress->product_id = $this->product->id;
        }
        return $productToAddress;
    }

    /**
     * @param $productToAddresses
     */
    public function setProductToAddresses($productToAddresses)
    {
        $this->_productToAddresses = [];
        foreach ($productToAddresses as $id => $productToAddress) {
            if (is_array($productToAddress)) {
                $this->_productToAddresses[$id] = $this->getProductToAddress($id);
                $this->_productToAddresses[$id]->setAttributes($productToAddress);
                if (!$this->_productToAddresses[$id]->quantity) {
                    $this->_productToAddresses[$id]->quantity = 0;
                }
            } elseif ($productToAddress instanceof ProductToAddress) {
                $this->_productToAddresses[$id] = $productToAddress;
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
    private function getAllModels()
    {
        $models = [
            'ProductShippingAddressQuantityForm' => $this,
        ];
        foreach ($this->productToAddresses as $id => $productToAddress) {
            $models['ProductToAddresses.' . $id] = $this->productToAddresses[$id];
        }
        return $models;
    }
}