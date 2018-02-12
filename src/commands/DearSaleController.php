<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\GearmanManager;
use app\models\Component;
use app\models\DearProduct;
use app\models\DearSale;
use app\models\Job;
use Yii;
use yii\console\Controller;
use yii\helpers\Json;

/**
 * Class DearSaleController
 * @package app\commands
 */
class DearSaleController extends Controller
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        //$this->run('dear-sale/export', [$cache]);
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @return int
     */
    public function actionExport()
    {
        $this->stdout('FINDING JOBS' . "\n");
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['or',
                ['>=', 'production_at', strtotime('-1 week')],
                ['>=', 'despatch_at', strtotime('-1 week')],
            ])
            ->orderBy(['production_at' => SORT_ASC]);
        $count = $jobs->count();

        foreach ($jobs->each(100) as $k => $job) {
            /** @var Job $job */
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($job->getTitle() . ' ');
            GearmanManager::runDearPush(DearSale::className(), $job->id);
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @return int
     */
    public function actionCleanup()
    {
        // get dear products
        $this->stdout('DOWNLOADING - ');
        //$sales = Yii::$app->dearApi->call('SaleList', 'GET', ['query' => ['limit' => 1, 'page' => 4200]]);
        $sales = Yii::$app->dearApi->getAllPages('SaleList', 'GET', [], 1000);

        // cleanup
        $this->stdout('PREPARING DATA - ');
        $badSales = [];
        foreach ($sales['SaleList'] as $k => $sale) {
            if ($sale['Status'] == 'VOIDED') {
                $this->stdout('x');
                continue;
            }
            if (substr($sale['CustomerReference'], 0, 4) != 'job-') {
                $this->stdout('x');
                continue;
            }
            $job = Job::findOne(['vid' => substr($sale['CustomerReference'], 4)]);
            if (!$job) {
                $this->stdout('x');
                continue;
            }
            if (!$job->deleted_at && $job->status != 'job/cancelled' && $job->dearSale && $job->dearSale->dear_id == $sale['ID']) {
                $this->stdout('x');
                continue;
            }
            if (in_array($sale['ID'], ['76c9d6e0-441a-415a-bf06-8c914efe6c9b'])) {
                $this->stdout('x');
                continue;
            }
            $badSales[] = $sale;
            $this->stdout('.');
        }
        $this->stdout('DONE!' . "\n");

        // removing invalid sales
        $this->stdout('PREPARING DATA - ');
        $count = count($badSales);
        foreach ($badSales as $k => $sale) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($sale['ID'] . ' - ');
            $this->stdout($sale['CustomerReference'] . ' - ');
            if (substr($sale['CustomerReference'], 0, 4) == 'job-') {
                $job = Job::findOne(['vid' => substr($sale['CustomerReference'], 4)]);
                Yii::$app->dearApi->call('Sale', 'DELETE', [
                    'json' => [
                        'ID' => $sale['ID'],
                    ],
                ]);
                $this->stdout('DELETED - ');
            }
            $this->stdout('DONE!' . "\n");
        }

        return self::EXIT_CODE_NORMAL;
    }
}