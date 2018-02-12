<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\models\Address;
use app\models\Company;
use app\models\CompanyRate;
use app\models\CompanyRateOption;
use app\models\ContactToCompany;
use app\models\HubSpotCompany;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class CompanyRateForm
 * @package app\models\form
 *
 * @property CompanyRate $companyRate
 * @property CompanyRateOption[] $companyRateOptions
 */
class CompanyRateForm extends Model
{
    /**
     * @var CompanyRate
     */
    private $_companyRate;

    /**
     * @var CompanyRateOption[]
     */
    private $_companyRateOptions;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['CompanyRate'], 'required'],
            [['CompanyRateOptions'], 'safe'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->companyRate->validate()) {
            $error = true;
        }
        foreach ($this->companyRateOptions as $companyRateOption) {
            if (!$companyRateOption->validate()) {
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
        if (!$this->companyRate->save()) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveCompanyRateOptions()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();

        return true;
    }

    /**
     * @return bool
     */
    public function saveCompanyRateOptions()
    {
        $keep = [];
        foreach ($this->companyRateOptions as $companyRateOption) {
            $companyRateOption->company_rate_id = $this->companyRate->id;
            if (!$companyRateOption->save(false)) {
                return false;
            }
            $keep[] = $companyRateOption->id;
        }
        $query = CompanyRateOption::find()->andWhere(['company_rate_id' => $this->companyRate->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $companyRateOption) {
            $companyRateOption->delete();
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getCompanyRate()
    {
        return $this->_companyRate;
    }

    /**
     * @param $companyRate
     */
    public function setCompanyRate($companyRate)
    {
        if ($companyRate instanceof CompanyRate) {
            $this->_companyRate = $companyRate;
        } else if (is_array($companyRate)) {
            $this->companyRate->setAttributes($companyRate);
        }
    }

    /**
     * @return CompanyRateOption[]|array
     */
    public function getCompanyRateOptions()
    {
        if ($this->_companyRateOptions === null) {
            $this->_companyRateOptions = $this->companyRate->isNewRecord ? [] : $this->companyRate->companyRateOptions;
        }
        return $this->_companyRateOptions;
    }

    /**
     * @param $key
     * @return CompanyRateOption
     */
    private function getCompanyRateOption($key)
    {
        $companyRateOption = $key && strpos($key, 'new') === false ? CompanyRateOption::findOne($key) : false;
        if (!$companyRateOption) {
            $companyRateOption = new CompanyRateOption();
            $companyRateOption->loadDefaultValues();
        }
        return $companyRateOption;
    }

    /**
     * @param CompanyRateOption[]|array $companyRateOptions
     */
    public function setCompanyRateOptions($companyRateOptions)
    {
        unset($companyRateOptions['__id__']); // remove the hidden "new CompanyRateOption" row
        $this->_companyRateOptions = [];
        foreach ($companyRateOptions as $key => $companyRateOption) {
            if (is_array($companyRateOption)) {
                $this->_companyRateOptions[$key] = $this->getCompanyRateOption($key);
                $this->_companyRateOptions[$key]->setAttributes($companyRateOption);
            } elseif ($companyRateOption instanceof CompanyRateOption) {
                $this->_companyRateOptions[$companyRateOption->id] = $companyRateOption;
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
            'CompanyForm' => $this,
            'CompanyRate' => $this->companyRate,
        ];
        foreach ($this->companyRateOptions as $id => $companyRateOption) {
            $models['CompanyRateOption.' . $id] = $companyRateOption;
        }
        return $models;
    }
}