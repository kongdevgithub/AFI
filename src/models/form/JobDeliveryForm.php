<?php

namespace app\models\form;

use app\models\Address;
use app\models\Item;
use app\models\ItemToAddress;
use app\models\Job;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Model;

/**
 * JobDeliveryForm
 *
 * @property \app\models\ItemToAddress[] $itemToAddresses
 */
class JobDeliveryForm extends Model
{

    /**
     * @var Job
     */
    public $job;

    /**
     * @var array
     */
    public $quantity;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['quantity'], 'required'],
        ];
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

        foreach ($this->quantity as $item_id => $addresses) {
            foreach ($addresses as $address_id => $quantity) {
                $itemToAddress = $this->getItemToAddress($item_id, $address_id);
                $itemToAddress->quantity = $quantity;
                if (!$itemToAddress->save()) {
                    debug($itemToAddress->errors); die;
                    $transaction->rollBack();
                    return false;
                }
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @param $item_id
     * @param $address_id
     * @return ItemToAddress
     */
    private function getItemToAddress($item_id, $address_id)
    {
        $itemToAddress = ItemToAddress::findOne(['item_id' => $item_id, 'address_id' => $address_id]);
        if (!$itemToAddress) {
            $itemToAddress = new ItemToAddress();
            $itemToAddress->loadDefaultValues();
            $itemToAddress->item_id = $item_id;
            $itemToAddress->address_id = $address_id;
        }
        return $itemToAddress;
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
            'JobDeliveryForm' => $this,
        ];
        return $models;
    }
}