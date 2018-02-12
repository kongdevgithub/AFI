<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\GearmanManager;
use app\models\Company;
use app\models\Contact;
use app\models\HubSpotCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use app\models\HubSpotUser;
use app\models\Job;
use app\models\User;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\VarDumper;

/**
 * Class HubSpotController
 * @package app\commands
 */
class HubSpotController extends Controller
{
    /**
     * @param bool $cache
     * @return int
     */
    public function actionIndex($cache = true)
    {
        //$this->run('hub-spot/import-users', [$cache]);
        //$this->run('hub-spot/export-companies');
        //$this->run('hub-spot/export-contacts');
        //$this->run('hub-spot/import-companies');
        //$this->run('hub-spot/import-contacts');
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param bool $cache
     * @return int
     */
    public function actionImportUsers($cache = true)
    {
        $this->stdout("Importing Users\n");
        $hubSpotApi = Yii::$app->hubSpotApi;

        // download
        $cacheKey = 'hubSpotUsers-' . date('Y-m-d');
        $records = $cache ? Yii::$app->cacheFile->get($cacheKey) : false;

        if (!$records) {
            $this->stdout('downloading');
            $records = $hubSpotApi->cleanResponseData($hubSpotApi->client->owners()->all()->getData());
            Yii::$app->cacheFile->set($cacheKey, $records);
            $this->stdout(' done' . "\n");
        }

        // import
        $count = count($records);
        foreach ($records as $k => $record) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($record['email'] . ' - ');
            if ($record['email'] == 'info@afibranding.com.au') {
                $record['email'] = 'accounts@afibranding.com.au';
            }

            $user = User::findOne(['email' => $record['email']]);
            if ($user) {
                $hubSpotUser = HubSpotUser::find()->andWhere(['hub_spot_id' => $record['ownerId']])->one();
                if (!$hubSpotUser) {
                    $hubSpotUser = new HubSpotUser();
                    $hubSpotUser->hub_spot_id = (string)$record['ownerId'];
                    $this->stdout('created');
                } else {
                    $this->stdout('updated');
                }

                $hubSpotUser->model_name = $user->className();
                $hubSpotUser->model_id = $user->id;
                if (!$hubSpotUser->save()) {
                    print_r($hubSpotUser->errors);
                }
            } else {
                $this->stdout('skipped');
            }
            $this->stdout("\n");
        }

        return self::EXIT_CODE_NORMAL;
    }

    private function deleteCompanies()
    {
        $this->stdout("Deleting Companies\n");
        $hubSpotApi = Yii::$app->hubSpotApi;
        $companies = $hubSpotApi->client->companies()->all([
            'properties' => ['name', 'website'],
        ]);
        $data = $hubSpotApi->cleanResponseData($companies->getData()->companies);
        $count = count($data);
        foreach ($data as $k => $v) {
            echo CommandStats::stats($k + 1, $count);
            $this->stdout('deleted ' . $v['companyId'] . "\n");
            $hubSpotApi->client->companies()->delete($v['companyId']);
        }
    }

    public function actionExportCompanies()
    {
        $this->stdout("Exporting Companies\n");
        $companies = Company::find()->notDeleted()->all();
        $count = count($companies);
        foreach ($companies as $k => $company) {
            echo CommandStats::stats($k + 1, $count);
            echo GearmanManager::runHubSpotPush(HubSpotCompany::className(), $company->id);
            echo "\n";
        }
        return self::EXIT_CODE_NORMAL;
    }

    public function actionExportContacts()
    {
        $this->stdout("Exporting Contacts\n");
        $contacts = Contact::find()->notDeleted()->all();
        $count = count($contacts);
        foreach ($contacts as $k => $contact) {
            echo CommandStats::stats($k + 1, $count);
            echo GearmanManager::runHubSpotPush(HubSpotContact::className(), $contact->id);
            echo "\n";
        }
        return self::EXIT_CODE_NORMAL;
    }

    public function actionImportCompanies()
    {
        $this->stdout("Importing Companies\n");
        $companies = Company::find()->notDeleted()->all();
        $count = count($companies);
        foreach ($companies as $k => $company) {
            echo CommandStats::stats($k + 1, $count);
            HubSpotCompany::hubSpotPull($company->hubSpotCompany->hub_spot_id);
            echo "\n";
        }
        return self::EXIT_CODE_NORMAL;
    }

    public function actionImportContacts()
    {
        $this->stdout("Importing Contacts\n");
        $contacts = Contact::find()->notDeleted()->all();
        $count = count($contacts);
        foreach ($contacts as $k => $contact) {
            echo CommandStats::stats($k + 1, $count);
            HubSpotContact::hubSpotPull($contact->hubSpotContact->hub_spot_id);
            echo "\n";
        }
        return self::EXIT_CODE_NORMAL;
    }

    public function actionPushDeals()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->andWhere(['status' => [
                'job/quoteLost',
                'job/productionPending',
                'job/production',
                'job/despatch',
                'job/packed',
                'job/complete',
            ]])
            ->andWhere(['not', ['company_id' => 1]])
            ->andWhere(['>', 'id', '47600'])
            ->notDeleted();
        $count = $jobs->count();

        foreach ($jobs->each(100) as $k => $job) {
            $this->stdout(CommandStats::stats($k + 1, $count) . $job->id . "\n");
            try {
                $response = HubSpotDeal::hubSpotPush($job->id);
            } catch (\Exception $e) {
                //$this->stdout(VarDumper::export($response) . "\n");
            }
            sleep(1);
        }
        $this->stdout('DONE!' . "\n");
    }
}
