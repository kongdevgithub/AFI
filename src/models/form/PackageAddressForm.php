<?php

namespace app\models\form;

use app\models\Address;
use app\models\Job;
use app\models\Package;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class PackageAddressForm
 * @package app\models\form
 *
 * @property \app\models\Package $package
 * @property \app\models\Address $address
 */
class PackageAddressForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var Address
     */
    private $_address;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Address'], 'required'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;

        $needsAddress = false;
        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            if (!$package->address) {
                $needsAddress = true;
            }
        }
        if ($needsAddress) {
            if (!$this->address->validate()) {
                $error = true;
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

        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            $address = $package->address;

            // save address
            if (!$address) {
                $address = new Address();
                $address->loadDefaultValues();
                $address->model_name = $package->className();
                $address->model_id = $package->id;
            }

            if ($this->address->name)
                $address->name = $this->address->name;
            if ($this->address->street)
                $address->street = $this->address->street;
            if ($this->address->postcode)
                $address->postcode = $this->address->postcode;
            if ($this->address->city)
                $address->city = $this->address->city;
            if ($this->address->state)
                $address->state = $this->address->state;
            if ($this->address->country)
                $address->country = $this->address->country;
            if ($this->address->contact)
                $address->contact = $this->address->contact;
            if ($this->address->phone)
                $address->phone = $this->address->phone;
            if ($this->address->instructions)
                $address->instructions = $this->address->instructions;

            if (!$address->save(false)) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @return array
     */
    public function optsAddress()
    {
        $jobs = [];
        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            foreach ($package->units as $unit) {
                $job = $unit->item->product->job;
                $jobs[$job->id] = $job;
            }
        }
        $addresses = [];
        foreach ($jobs as $job) {
            $addresses = ArrayHelper::merge($addresses, ArrayHelper::map($job->addresses, 'id', 'label'));
        }
        return $addresses;
    }

    /**
     * @return Job|bool
     */
    public function getDefaultJob()
    {
        $job = false;
        foreach ($this->ids as $id) {
            $package = Package::findOne($id);
            foreach ($package->units as $unit) {
                $job = $unit->item->product->job;
                break(2);
            }
        }
        return $job;
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
            'PackageAddressForm' => $this,
            'Address' => $this->address,
        ];
        return $models;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        if (!$this->_address) {
            $this->_address = new Address;
            $this->_address->loadDefaultValues();
        }
        return $this->_address;
    }

    /**
     * @param Address|array $address
     */
    public function setAddress($address)
    {
        if ($address instanceof Address) {
            $this->_address = $address;
        } else if (is_array($address)) {
            $this->address->setAttributes($address);
        }
    }

}