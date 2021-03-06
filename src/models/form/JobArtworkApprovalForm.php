<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\models\Address;
use app\models\HubSpotDeal;
use app\models\Job;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class JobArtworkApprovalForm
 * @package app\models\form
 *
 * @property \app\models\Job $job
 */
class JobArtworkApprovalForm extends Model
{

    /**
     * @var string
     */
    public $full_name;

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
            [['full_name'], 'required'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
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

        foreach ($this->job->products as $product) {
            foreach ($product->items as $item) {
                if (explode('/', $item->status)[1] == 'approval') {
                    $item->artwork_approved_by = $this->full_name;
                    $item->status = $item->getNextStatus();
                    if (!$item->save(false)) {
                        $transaction->rollBack();
                        return false;
                    }
                }
            }
        }

        $transaction->commit();

        // push the deal back to hubspot
        GearmanManager::runHubSpotPush(HubSpotDeal::className(), $this->job->id);

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
            'JobQuoteApprovalForm' => $this,
            'Job' => $this->job,
        ];
        return $models;
    }
}