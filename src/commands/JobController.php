<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\EmailManager;
use app\components\GearmanManager;
use app\components\Helper;
use app\models\Company;
use app\models\HubSpotDeal;
use app\models\Job;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class JobController
 * @package app\commands
 */
class JobController extends Controller
{


    /**
     *
     */
    public function actionSetVids()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->andWhere(['>=', 'created_at', strtotime('-7days')])
            ->notDeleted();
        $count = $jobs->count();

        foreach ($jobs->each(100) as $k => $job) {
            //$this->stdout(CommandStats::stats($k + 1, $count));
            $vid = $job->generateVid();
            if ($job->vid != $vid) {
                $job->vid = $vid;
                //$this->stdout('assigning vid-' . $job->vid . ' to job-' . $job->id);
                $this->stdout($job->vid);
                $job->save(false);
            } else {
                $this->stdout('.');
            }
            //$this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionFixUnitCount()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['status' => ['job/draft', 'job/quote', 'job/productionPending', 'job/production', 'job/despatch']]);
        $count = $jobs->count();

        foreach ($jobs->each(100) as $k => $job) {
            /** @var Job $job */
            $this->stdout(CommandStats::stats($k + 1, $count));
            while (!$job->checkUnitCount()) {
                $this->stdout('fixing job-' . $job->vid . ' ');
                $job->fixUnitCount();
                $job->refresh();
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionFixTotals()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['quote_generated' => 1])
            ->andWhere(['status' => ['job/draft', 'job/quote', 'job/productionPending', 'job/production', 'job/despatch']]);
        //$count = $jobs->count();

        $this->stdout(implode(',', [
                'vid',
                'wholesale_price',
                'product_total',
                'status',
                'invoice_amount',
            ]) . "\n");
        foreach ($jobs->each(1000) as $k => $job) {
            /** @var Job $job */
            //$this->stdout(CommandStats::stats($k + 1, $count));
            if (!$job->checkTotals()) {
                //$this->stdout('fixing job-' . $job->vid . ' ');

                $productTotal = 0;
                foreach ($job->products as $product) {
                    $productTotal += $product->quote_factor_price - $product->quote_discount_price;
                }
                $this->stdout(implode(',', [
                        $job->vid,
                        $job->quote_wholesale_price,
                        $productTotal,
                        explode('/', $job->status)[1],
                        $job->invoice_amount,
                    ]) . "\n");

                EmailManager::sendQuoteTotalsCheckAlert($job);
                $job->resetQuoteGenerated();
            }
            //$this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionSetDates()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted();
        $count = $jobs->count();

        foreach ($jobs->each(100) as $k => $job) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout('assigning dates to job-' . $job->vid . "\n");
            $job->despatch_date = Helper::getRelativeDate($job->due_date, $job->freight_days * -1, false);
            $job->prebuild_date = Helper::getRelativeDate($job->despatch_date, $job->prebuild_days * -1);
            $job->production_date = Helper::getRelativeDate($job->prebuild_date, $job->production_days * -1);
            $job->save(false);
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionLoseOldDrafts()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['status' => 'job/draft'])
            ->andWhere(['<=', 'created_at', strtotime('-90days')])
            ->all();
        $count = count($jobs);

        foreach ($jobs as $k => $job) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout('archiving job-' . $job->vid . ' (' . Yii::$app->formatter->asDate($job->created_at) . ')' . "\n");
            $job->status = 'job/quoteLost';
            $job->quote_lost_reason = 'phantom';
            $job->save(false);
            GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionLoseOldQuotes()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['status' => 'job/quote'])
            ->andWhere(['<=', 'quote_at', strtotime('-90days')])
            ->all();
        $count = count($jobs);

        foreach ($jobs as $k => $job) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout('archiving job-' . $job->vid . ' (' . Yii::$app->formatter->asDate($job->created_at) . ')' . "\n");
            $job->status = 'job/quoteLost';
            $job->quote_lost_reason = 'phantom';
            $job->save(false);
            GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     *
     */
    public function actionLoseVersionQuotes()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['status' => 'job/quote'])
            ->andWhere('CAST(id AS CHAR) <> vid')// id <> vid doesn't work
            ->orderBy(['vid' => SORT_ASC])
            ->all();

        $count = count($jobs);
        foreach ($jobs as $k => $job) {
            $job->refresh();
            if ($job->status != 'job/quote') continue;

            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout('checking job-' . $job->vid);
            foreach ($job->getForkVersionVids() as $job_id => $job_vid) {
                if ($job_id == $job->id) continue;
                $_job = Job::findOne($job_id);
                if (in_array($_job->status, ['job/quote', 'job/productionPending', 'job/production', 'job/prebuild', 'job/despatch', 'job/packed'])) {
                    $this->stdout(' found ' . $_job->vid . '=' . explode('/', $_job->status)[1]);
                    $job->status = 'job/quoteLost';
                    $job->quote_lost_reason = 'phantom';
                    $job->save(false);
                    GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
                    break;
                }
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

    public function actionAssignStaff()
    {
        $this->stdout('FINDING REPS' . "\n");
        $reps = [
            42, // aron@afibranding.com.au
        ];
        foreach ($reps as $user_id) {
            $companies = Company::find()
                ->notDeleted()
                ->andWhere(['staff_rep_id' => $user_id])
                ->all();

            $jobs = Job::find()
                ->notDeleted()
                ->andWhere(['company_id' => ArrayHelper::map($companies, 'id', 'id')])
                ->all();
            $count = count($jobs);

            foreach ($jobs as $k => $job) {
                $this->stdout(CommandStats::stats($k + 1, $count));
                $this->stdout($job->id);
                if ($job->staff_lead_id != $user_id) {
                    $job->staff_lead_id = $user_id;
                    $job->save(false);
                    GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
                    $this->stdout(' - updated');
                }
                $this->stdout("\n");
            }
        }
        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }
}
