<?php

namespace app\models\form;

use app\models\Address;
use app\models\Job;
use app\models\Package;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PackageForm
 * @package app\models\form
 *
 * @property \app\models\Package $package
 * @property \app\models\Address $address
 */
class PackageForm extends Model
{
    /**
     * @var int
     */
    public $quantity;
    /**
     * @var Package
     */
    private $_package;
    /**
     * @var Address
     */
    private $_address;

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['PackageForm'])) {
            foreach ($values['PackageForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['PackageForm']);
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Package', 'Address', 'quantity'], 'required'],
            [['quantity'], 'number', 'min' => 1],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'default' => ['Package', 'Address'],
            'overflow' => ['Package', 'Address', 'quantity'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->package->validate()) {
            $error = true;
        }
        if (!$this->address->validate()) {
            $error = true;
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

        $post = Yii::$app->request->post();

        $transaction = Yii::$app->dbData->beginTransaction();

        for ($i = 0; $i < $this->quantity; $i++) {

            // reset
            $this->package = $post['Package'];
            $this->package->id = null;
            $this->package->isNewRecord = true;
            $this->address = $post['Address'];
            $this->address->id = null;
            $this->address->isNewRecord = true;

            // save
            if (!$this->package->save()) {
                $transaction->rollBack();
                return false;
            }
            $this->address->model_name = $this->package->className();
            $this->address->model_id = $this->package->id;
            $this->address->type = 'delivery';
            if (!$this->address->save()) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->_package;
    }

    /**
     * @param $package
     */
    public function setPackage($package)
    {
        if ($package instanceof Package) {
            $this->_package = $package;
        } else if (is_array($package)) {
            $this->_package->setAttributes($package);
        }
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        if (!$this->_address) {
            if ($this->package->address) {
                $this->_address = $this->package->address;
            } else {
                $this->_address = new Address();
                $this->_address->loadDefaultValues();
            }
        }
        return $this->_address;
    }

    /**
     * @param Address|array $address
     */
    public function setAddress($address)
    {
        if (is_array($address)) {
            $this->address->setAttributes($address);
        } elseif ($address instanceof Address) {
            $this->_address = $address;
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
            'PackageForm' => $this,
            'Package' => $this->package,
            'Address' => $this->address,
        ];
        return $models;
    }


    /**
     * @return array
     */
    public function optsAddress()
    {
        $jobs = [];
        foreach ($this->package->units as $unit) {
            $job = $unit->item->product->job;
            $jobs[$job->id] = $job;
        }
        $addresses = [];
        foreach ($jobs as $job) {
            $addresses = ArrayHelper::merge($addresses, ArrayHelper::map($job->addresses, 'id', 'label'));
        }
        return $addresses;
    }

}