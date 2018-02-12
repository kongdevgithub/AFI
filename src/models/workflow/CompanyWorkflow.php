<?php

namespace app\models\workflow;

use app\components\Helper;
use app\models\Company;
use app\models\Job;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;
use yii\helpers\Html;

/**
 * CompanyWorkflow
 * @package app\models\workflow
 */
class CompanyWorkflow extends BaseWorkflow
{

    /**
     * @param Company $company
     * @param WorkflowEvent $event
     */
    public static function afterEnter_suspended($company, $event)
    {
        // move production jobs to suspended
        $messages = [
            'info' => [],
            'danger' => [],
        ];
        foreach ($company->getJobs()->andWhere(['status' => 'job/production'])->all() as $job) {
            /** @var Job $job */
            $job->status = 'job/suspended';
            if ($job->save(false)) {
                $messages['info'][] = Html::a('job-' . $job->id, ['/job/view', 'id' => $job->id], ['target' => '_blank']) . ' - ' . $job->name;
            } else {
                $messages['danger'][] = Html::a('job-' . $job->id, ['/job/view', 'id' => $job->id], ['target' => '_blank']) . ' - ' . $job->name . ' - ' . Helper::getErrorString($job);
            }
        }
        if (!empty($messages['info'])) {
            Yii::$app->getSession()->addFlash('info', Yii::t('app', 'The following jobs have been suspended: {jobs}', ['jobs' => Html::ul($messages['info'], ['encode' => false])]));
        }
        if (!empty($messages['danger'])) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'The following jobs could not be suspended: {jobs}', ['jobs' => Html::ul($messages['danger'], ['encode' => false])]));
        }
    }

    /**
     * @param Company $company
     * @param WorkflowEvent $event
     */
    public static function afterEnter_active($company, $event)
    {
        //// move suspended jobs to production
        //$messages = [
        //    'info' => [],
        //    'danger' => [],
        //];
        //foreach ($company->getJobs()->andWhere(['status' => 'job/suspended'])->all() as $job) {
        //    /** @var Job $job */
        //    $job->status = 'job/production';
        //    if ($job->save(false)) {
        //        $messages['info'][] = Html::a('job-' . $job->id, ['/job/view', 'id' => $job->id], ['target' => '_blank']) . ' - ' . $job->name;
        //    } else {
        //        $messages['danger'][] = Html::a('job-' . $job->id, ['/job/view', 'id' => $job->id], ['target' => '_blank']) . ' - ' . $job->name . ' - ' . Helper::getErrorString($job);
        //    }
        //}
        //if (!empty($messages['info'])) {
        //    Yii::$app->getSession()->addFlash('info', Yii::t('app', 'The following jobs have been resumed: {jobs}', ['jobs' => Html::ul($messages['info'], ['encode' => false])]));
        //}
        //if (!empty($messages['danger'])) {
        //    Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'The following jobs could not be resumed: {jobs}', ['jobs' => Html::ul($messages['danger'], ['encode' => false])]));
        //}
    }
}