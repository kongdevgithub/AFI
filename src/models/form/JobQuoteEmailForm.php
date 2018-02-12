<?php
namespace app\models\form;

use app\components\EmailManager;
use app\models\Address;
use app\models\HubSpotDeal;
use app\models\Job;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class JobQuoteEmailForm
 * @package app\models\form
 *
 * @property \app\models\Job $job
 */
class JobQuoteEmailForm extends Model
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
            [['Job', 'email_address'], 'required'],
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
     */
    public function send()
    {
        if (!$this->validate()) {
            return false;
        }

        EmailManager::sendQuoteApproval($this->job, $this->email_address);

        //$this->job->status = 'job/quote';
        //$transaction = Yii::$app->dbData->beginTransaction();
        //if (!$this->job->save()) {
        //    $transaction->rollBack();
        //    return false;
        //}
        //$transaction->commit();

        //// push the deal back to hubspot
        //GearmanManager::runHubSpotPush(HubSpotDeal::className(), $this->job->id);

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
            'JobQuoteEmailForm' => $this,
            'Job' => $this->job,
        ];
        return $models;
    }
}