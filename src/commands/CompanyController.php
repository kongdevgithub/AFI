<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\Csv;
use app\components\Helper;
use app\models\Company;
use app\models\CompanyRate;
use app\models\CompanyRateOption;
use app\models\HubSpotCompany;
use app\models\Job;
use Yii;
use yii\console\Controller;

/**
 * Class CompanyController
 * @package app\commands
 */
class CompanyController extends Controller
{
    /**
     *
     */
    public function actionFixWebsite()
    {
        $companies = Company::find()->notDeleted()->all();

        $websites = [];
        $contacts = Csv::csvToArray(Yii::$app->runtimePath . '/contacts.csv');
        foreach ($contacts as $contact) {
            if (trim($contact['Website'])) {
                $key = trim(strtolower($contact['Account Name']));
                $websites[$key] = trim($contact['Website']);
            }
        }

        foreach ($companies as $company) {
            if (!$company->website) {
                $key = trim(strtolower($company->name));
                if (!empty($websites[$key])) {
                    $company->website = $websites[$key];
                    if ($company->save(false)) {
                        HubSpotCompany::hubSpotPush($company->id);
                    }
                }
            }
        }
    }

    /**
     *
     */
    public function actionSetJobDueDatesV3()
    {
        $this->stdout('FINDING COMPANIES WITH NO FIRST JOB DATE' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere(['first_job_due_date' => null]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $v3company = \app\models\v3\Company::find()
                ->andWhere(['name' => $company->name])
                ->one();
            if ($v3company) {
                $this->stdout(' - found v3 company');
                $v3firstJob = $v3company->getJobs()
                    ->andWhere(['status' => 'swJob.Collected'])
                    ->orderBy(['id' => SORT_ASC])
                    ->one();
                if ($v3firstJob) {
                    $this->stdout(' - found v3 first job');
                    $company->first_job_due_date = $v3firstJob->despatch_requested ?: $v3firstJob->created;
                    $company->save(false);
                }
                $v3lastJob = $v3company->getJobs()
                    ->andWhere(['status' => 'swJob.Collected'])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                if ($v3firstJob) {
                    $this->stdout(' - found v3 last job');
                    $company->last_job_due_date = $v3lastJob->despatch_requested ?: $v3lastJob->created;
                    $company->save(false);
                }
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

    /**
     *
     */
    public function actionSetJobDueDates()
    {
        $this->actionSetFirstJobDueDate();
        $this->actionSetLastJobDueDate();
        $this->actionResetOldJobDueDates();
    }

    public function actionResetOldJobDueDates()
    {
        $this->stdout('FINDING COMPANIES WITH OLD LAST JOB DATE' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere(['<=', 'last_job_due_date', date('Y-m-d', strtotime('-3years'))]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout(' - reset old trader');
            $company->last_job_due_date = null;
            $company->first_job_due_date = null;
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

    public function actionSetFirstJobDueDate()
    {
        $this->stdout('FINDING COMPANIES WITH NO FIRST JOB DATE' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere(['first_job_due_date' => null]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            /** @var Job $firstJob */
            $firstJob = $company->getJobs()
                ->andWhere(['status' => 'job/complete'])
                ->andWhere(['>=', 'due_date', date('Y-m-d', strtotime('-3years'))])
                ->orderBy(['id' => SORT_ASC])
                ->one();
            if ($firstJob) {
                $this->stdout(' - found v4 first job');
                $company->first_job_due_date = $firstJob->due_date;
                $company->save(false);
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }


    public function actionSetLastJobDueDate()
    {
        $this->stdout('FINDING COMPANIES WITH DATE OLDER THAN 1 MONTH' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere(['<=', 'last_job_due_date', date('Y-m-d', strtotime('-1month'))]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            /** @var Job $job */
            $job = $company->getJobs()
                ->andWhere(['status' => 'job/complete'])
                ->andWhere(['>=', 'due_date', date('Y-m-d', strtotime('-1month'))])
                ->orderBy(['due_date' => SORT_DESC])
                ->one();
            if ($job) {
                $this->stdout(' - found v4 last job');
                $company->last_job_due_date = $job->due_date;
                $company->save(false);
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

    public function actionSetStaffRep()
    {
        $this->stdout('FINDING COMPANIES WITH MISSING STAFF REP' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere([
                'or',
                ['staff_rep_id' => 34], // accounts@afibranding.com.au (helen)
                ['staff_rep_id' => null],
            ]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            /** @var Company $company */
            $this->stdout($company->id . ': ' . $company->name);
            $company->staff_rep_id = Job::STAFF_LEAD_DEFAULT;
            $company->save(false);
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

    /**
     *
     */
    public function actionConvertRates()
    {
        $this->stdout('FINDING COMPANIES WITH RATES' . "\n");
        $companies = Company::find()
            ->notDeleted()
            ->andWhere(['not', ['rates_encoded' => null]])
            ->andWhere(['not', ['rates_encoded' => '']]);
        $count = $companies->count();
        foreach ($companies->all() as $k => $company) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            /** @var Company $company */
            $this->stdout($company->id . ': ' . $company->name);

            //foreach ($company->companyRates as $companyRate) {
            //    $companyRate->delete();
            //    $this->stdout('x');
            //}

            foreach ($company->getRates() as $rate) {
                foreach ($rate['prices'] as $component_id => $price) {
                    if (is_array($price)) {
                        foreach ($price as $_size => $_price) {
                            $this->saveCompanyRate($company, $rate, $component_id, $_price, $_size);
                        }
                    } else {
                        $this->saveCompanyRate($company, $rate, $component_id, $price);
                    }
                }
            }

            //$company->rates_encoded = null;
            //$company->save(false);
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

    /**
     * @param $company
     * @param $rate
     * @param $component_id
     * @param $price
     * @param null $size
     */
    private function saveCompanyRate($company, $rate, $component_id, $price, $size = null)
    {
        if (!$size || $size == '*') $size = null;

        $companyRate = CompanyRate::find()
            ->notDeleted()
            ->andWhere([
                'company_id' => $company->id,
                'product_type_id' => $rate['product_type_id'],
                'item_type_id' => $rate['item_type_id'],
                'option_id' => $rate['option_id'],
                'component_id' => $component_id,
                'size' => $size,
            ])
            ->one();
        if (!$companyRate) {
            $this->stdout('+');
            $companyRate = new CompanyRate();
            $companyRate->company_id = $company->id;
            $companyRate->product_type_id = $rate['product_type_id'];
            $companyRate->item_type_id = $rate['item_type_id'];
            $companyRate->option_id = $rate['option_id'];
            $companyRate->component_id = $component_id;
            $companyRate->size = $size;
        }
        $companyRate->price = $price;
        if (!$companyRate->save()) {
            $this->stdout('cannot save CompanyRate: ' . Helper::getErrorString($companyRate));
        }
        if (!empty($rate['required_options'])) {
            foreach ($rate['required_options'] as $required_option_id => $required_component_id) {
                $companyRateOption = CompanyRateOption::find()
                    ->notDeleted()
                    ->andWhere([
                        'company_rate_id' => $companyRate->id,
                        'option_id' => $required_option_id,
                        'component_id' => $required_component_id,
                    ])
                    ->one();
                if (!$companyRateOption) {
                    $this->stdout('.');
                    $companyRateOption = new CompanyRateOption();
                    $companyRateOption->company_rate_id = $companyRate->id;
                    $companyRateOption->option_id = $required_option_id;
                    $companyRateOption->component_id = $required_component_id;
                    if (!$companyRateOption->save()) {
                        $this->stdout('cannot save CompanyRateOption: ' . Helper::getErrorString($companyRateOption));
                    }
                }
            }
        }
    }

}
