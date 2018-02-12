<?php

namespace app\models\form;

use app\models\Address;
use app\models\Item;
use app\models\ItemToAddress;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Model;

/**
 * ItemShippingAddressQuantityForm
 *
 * @property \app\models\ItemToAddress[] $itemToAddresses
 */
class ItemShippingAddressQuantityForm extends Model
{

    /**
     * @var Item
     */
    public $item;

    /**
     * @var array
     */
    public $quantity;

    /**
     * @var ItemToAddress[]
     */
    private $_itemToAddresses;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['ItemToAddresses'], 'required'],
        ];
    }

    public function afterValidate()
    {
        $error = false;
        foreach ($this->itemToAddresses as $itemToAddress) {
            if (!$itemToAddress->validate()) {
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
        if (!$this->saveItemToAddresses()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    /**
     * @return bool
     */
    public function saveItemToAddresses()
    {
        $keep = [];
        foreach ($this->itemToAddresses as $itemToAddress) {
            $itemToAddress->item_id = $this->item->id;
            if (!$itemToAddress->save(false)) {
                return false;
            }
            $keep[] = $itemToAddress->id;
        }
        $query = ItemToAddress::find()->andWhere(['item_id' => $this->item->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $item) {
            $item->delete();
        }
        return true;
    }

    /**
     * @return ItemToAddress[]
     */
    public function getItemToAddresses()
    {
        if ($this->_itemToAddresses === null) {
            $this->_itemToAddresses = [];
            foreach ($this->item->product->job->addresses as $address) {
                if ($address->type != Address::TYPE_SHIPPING) continue;
                $this->_itemToAddresses[] = $this->getItemToAddress($address->id);
            }

        }
        return $this->_itemToAddresses;
    }

    /**
     * @param $id
     * @return ItemToAddress|bool
     */
    private function getItemToAddress($id)
    {
        $itemToAddress = $id ? ItemToAddress::findOne(['address_id' => $id, 'item_id' => $this->item->id]) : false;
        if (!$itemToAddress) {
            $itemToAddress = new ItemToAddress();
            $itemToAddress->loadDefaultValues();
            $itemToAddress->address_id = $id;
            $itemToAddress->item_id = $this->item->id;
        }
        return $itemToAddress;
    }

    /**
     * @param $itemToAddresses
     */
    public function setItemToAddresses($itemToAddresses)
    {
        $this->_itemToAddresses = [];
        foreach ($itemToAddresses as $id => $itemToAddress) {
            if (is_array($itemToAddress)) {
                $this->_itemToAddresses[$id] = $this->getItemToAddress($id);
                $this->_itemToAddresses[$id]->setAttributes($itemToAddress);
                if (!$this->_itemToAddresses[$id]->quantity) {
                    $this->_itemToAddresses[$id]->quantity = 0;
                }
            } elseif ($itemToAddress instanceof ItemToAddress) {
                $this->_itemToAddresses[$id] = $itemToAddress;
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
            'ItemShippingAddressQuantityForm' => $this,
        ];
        foreach ($this->itemToAddresses as $id => $itemToAddress) {
            $models['ItemToAddresses.' . $id] = $this->itemToAddresses[$id];
        }
        return $models;
    }
}