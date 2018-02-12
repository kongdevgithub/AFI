<?php

namespace app\models\form;

use app\components\EmailManager;
use app\models\Job;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class JobInvoiceEmailForm
 * @package app\models\form
 *
 * @property \app\models\Job $job
 */
class JobInvoiceEmailForm extends Model
{

    /**
     * @var string
     */
    public $email_address;

    /**
     * @var Job
     */
    private $_job;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Job'], 'required'],
            [['email_address'], 'email'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if (!$this->job->validate()) {
            $error = true;
        }
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function send()
    {
        if (!$this->validate()) {
            return false;
        }

        EmailManager::sendJobInvoice($this->job, $this->email_address);
        return true;
    }

    /**
     * @return mixed
     */
    public function getJob()
    {
        return $this->_job;
    }

    /**
     * @param $job
     */
    public function setJob($job)
    {
        if ($job instanceof Job) {
            $this->_job = $job;
        } else if (is_array($job)) {
            $this->_job->setAttributes($job);
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
            'JobInvoiceEmailForm' => $this,
            'Job' => $this->job,
        ];
        return $models;
    }
}