<?php
/**
 * Created by PhpStorm.
 * User: Brett
 * Date: 14/09/2016
 * Time: 3:33 PM
 */

namespace app\commands;


use app\components\BulkQuoteHelper;
use app\components\CommandStats;
use app\components\GearmanManager;
use app\models\Job;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\console\Controller;

/**
 * Class BulkController
 * @package app\commands
 */
class BulkController extends Controller
{

    /**
     *
     */
    public function actionIndex()
    {
        $this->actionBuild();
        $this->actionQuote();
    }

    /**
     * @param bool $filter
     */
    public function actionReQuote($filter = false)
    {
        $this->stdout('RESETTING JOBS' . "\n");
        $jobs = BulkQuoteHelper::getJobs($filter);
        $count = count($jobs);
        $i = 0;
        foreach ($jobs as $k => $_job) {
            $i++;
            $job = BulkQuoteHelper::getJob('TEST: ' . $k);
            $this->stdout(CommandStats::stats($i, $count));
            $this->stdout('job-' . $job->id . "\n");
            $job->quote_generated = 0;
            $job->save(false);
        }
    }

    /**
     * @param bool $filter
     * @throws \Exception
     */
    public function actionDelete($filter = false)
    {
        $this->stdout('DELETING JOBS' . "\n");
        //$jobs = Job::find()->notDeleted()->all();
        //$count = count($jobs);
        //foreach ($jobs as $k => $job) {
        //    $this->stdout(CommandStats::stats($k + 1, $count));
        //    $job->delete();
        //    $this->stdout('deleted' . "\n");
        //}
        //die;
        $jobs = BulkQuoteHelper::getJobs($filter);
        $count = count($jobs);
        $i = 0;
        foreach ($jobs as $k => $_job) {
            $i++;
            $job = BulkQuoteHelper::getJob('TEST: ' . $k);
            $this->stdout(CommandStats::stats($i, $count));
            if (!$job) {
                $this->stdout('no job' . "\n");
                continue;
            }
            $this->stdout('job-' . $job->id . "\n");
            $job->delete();
        }
    }

    /**
     * @param bool $filter
     */
    public function actionBuild($filter = false)
    {
        $this->stdout('BUILDING JOBS' . "\n");
        $created = 0;
        $jobs = BulkQuoteHelper::getJobs($filter);
        $count = count($jobs);
        $i = 0;
        foreach ($jobs as $k => $_job) {
            $i++;
            $this->stdout(CommandStats::stats($i, $count));
            $this->stdout($k . "\n");
            //$job = BulkQuoteHelper::getJob('TEST: ' . $k, $_job);
            GearmanManager::runJobBuild($k, $_job);
            //if ($job->isNewRecord) {
            //$created++;
            //if ($created > $limit) {
            //    return;
            //}
            //}
        }
    }

    /**
     * @param bool|string $filter
     */
    public function actionQuote($filter = false)
    {
        $this->stdout('QUOTING JOBS' . "\n");
        $quoted = 0;
        $jobs = BulkQuoteHelper::getJobs($filter);
        $count = count($jobs);
        $i = 0;
        foreach ($jobs as $k => $_job) {
            $i++;
            $this->stdout(CommandStats::stats($i, $count));
            $this->stdout($k . "\n");
            $job = BulkQuoteHelper::getJob('TEST: ' . $k, $_job);
            //$job->quote_generated = 0;
            //$job->save(false);
            if (!$job->quote_generated) {
                $job->spoolQuote();
                //BulkQuoteHelper::loadQuote($job);
            }
        }
    }

}