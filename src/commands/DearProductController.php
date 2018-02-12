<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\GearmanManager;
use app\models\Component;
use app\models\DearProduct;
use Yii;
use yii\console\Controller;

/**
 * Class DearProductController
 * @package app\commands
 */
class DearProductController extends Controller
{
    /**
     * @param bool $cache
     * @return int
     */
    public function actionIndex($cache = true)
    {
        //$this->run('dear-inventory/import', [$cache]);
        //$this->run('dear-inventory/export', [$cache]);
        return self::EXIT_CODE_NORMAL;
    }

//    public function actionTest()
//    {
//        $dearApi = Yii::$app->dearApi;
//        //$response = $dearApi->call('ProductCategories', 'GET', ['query' => ['limit' => 100, 'page' => 1]]);
//        //print_r($response);
//
//        $response = $dearApi->call('ProductCategories', 'PUT', ['data' => [
//            'Id' => '293ce6fc-ca8b-412b-8e09-6857a63df814',
//            'Name' => 'Cleanroom',
//        ]]);
//        print_r($response);
//    }

    /**
     * @return int
     */
    public function actionImport()
    {
        $this->stdout("Importing\n");

        // get dear products
        $this->stdout('downloading');
        //$products = $dearApi->call('Products', 'GET', ['query' => ['limit' => 20, 'page' => 1]]);
        $products = Yii::$app->dearApi->getAllPages('Products', 'GET');
        $this->stdout(' done' . "\n");

        // import
        $count = count($products['Products']);
        foreach ($products['Products'] as $k => $product) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($product['ID'] . ' - ' . $product['SKU'] . ' - ');
            DearProduct::dearPull($product['ID'], $product);
            $this->stdout('DONE!' . "\n");
        }

        return self::EXIT_CODE_NORMAL;
    }


    /**
     * @return int
     */
    public function actionExport()
    {
        $this->stdout("Exporting\n");

        // get console components
        $components = Component::find()
            ->notDeleted()
            //->andWhere(['code' => 'SS101BL'])
            ->all();

        // export
        $count = count($components);
        foreach ($components as $k => $component) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($component->id . ' - ' . $component->code . ' - ');
            GearmanManager::runDearPush(DearProduct::className(), $component->id);
            sleep(1);
            $this->stdout('DONE!' . "\n");
        }

        return self::EXIT_CODE_NORMAL;
    }


    /**
     * @return int
     */
    public function actionUpdateCosts()
    {
        $this->stdout("Updating Components\n");

        $codes = explode("\n", trim(file_get_contents(Yii::getAlias('@data/octanorm/factor-3.txt'))));
        $count = count($codes);
        foreach ($codes as $k => $code) {
            $this->stdout(CommandStats::stats($k + 1, $count) . ' - ' . trim($code));
            $component = Component::findOne(['code' => trim($code)]);
            if (!$component) {
                $this->stdout(' - not found' . "\n");
                continue;
            }
            if ($component->quantity_factor != '0 2') {
                $this->stdout(' - already updated' . "\n");
                continue;
            }
            $component->quantity_factor = '0 3';
            $component->unit_cost = $component->unit_cost / 3 * 2;
            $component->save(false);
            GearmanManager::runDearPush(DearProduct::className(), $component->id);
            sleep(1);
            $this->stdout(' - DONE!' . "\n");
        }

        $codes = explode("\n", trim(file_get_contents(Yii::getAlias('@data/octanorm/factor-2.15.txt'))));
        $count = count($codes);
        foreach ($codes as $k => $code) {
            $this->stdout(CommandStats::stats($k + 1, $count) . ' - ' . trim($code));
            $component = Component::findOne(['code' => trim($code)]);
            if (!$component) {
                $this->stdout(' - not found' . "\n");
                continue;
            }
            if ($component->quantity_factor != '0 2') {
                $this->stdout(' - already updated' . "\n");
                continue;
            }
            $component->quantity_factor = '0 2.15';
            $component->unit_cost = $component->unit_cost / 2.15 * 2;
            $component->save(false);
            GearmanManager::runDearPush(DearProduct::className(), $component->id);
            sleep(1);
            $this->stdout(' - DONE!' . "\n");
        }


        return self::EXIT_CODE_NORMAL;
    }

}