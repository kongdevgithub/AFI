<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\models\Address;
use app\models\Company;
use app\models\ContactToCompany;
use app\models\HubSpotCompany;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class CompanyForm
 * @package app\models\form
 *
 * @property \app\models\Company $company
 * @property \app\models\Address $address
 */
class CompanyForm extends Model
{
    /**
     * @var Company
     */
    private $_company;
    /**
     * @var Address
     */
    private $_address;

    /**
     * @var
     */
    public $no_website;

    /**
     * @var
     */
    public $attachments;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Company', 'Address'], 'required'],
            [['no_website'], 'safe'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->company->validate()) {
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
        $transaction = Yii::$app->dbData->beginTransaction();
        if (!$this->company->save()) {
            $transaction->rollBack();
            return false;
        }
        $this->address->model_name = $this->company->className();
        $this->address->model_id = $this->company->id;
        if (!$this->address->save()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();

        // push the company back to hubspot
        GearmanManager::runHubSpotPush(HubSpotCompany::className(), $this->company->id);

        return true;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->_company;
    }

    /**
     * @param $company
     */
    public function setCompany($company)
    {
        if ($company instanceof Company) {
            $this->_company = $company;
        } else if (is_array($company)) {
            $this->company->setAttributes($company);
        }
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        if ($this->_address === null) {
            $this->_address = $this->company->billingAddress;
            if (!$this->_address) {
                $this->_address = new Address();
                $this->_address->loadDefaultValues();
                $this->_address->type = Address::TYPE_BILLING;
            }
        }
        return $this->_address;
    }

    /**
     * @param $address
     */
    public function setAddress($address)
    {
        if ($address instanceof Address) {
            $this->_address = $address;
        } else if (is_array($address)) {
            $this->address->setAttributes($address);
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
        return [
            'CompanyForm' => $this,
            'Company' => $this->company,
            'Address' => $this->address,
        ];
    }
}