<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\Csv;
use app\components\GearmanManager;
use app\models\Component;
use app\models\DearProduct;
use Yii;
use yii\console\Controller;

/**
 * Class ComponentController
 * @package app\commands
 */
class ComponentController extends Controller
{
    /**
     * @param bool $cache
     * @return int
     */
    public function actionIndex($cache = true)
    {
        //$this->run('component/import-stock', [$cache]);
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @return int
     */
    public function actionImportStock()
    {
        $this->stdout("IMPORTING STOCK\n");
        $csv = Csv::csvToArray(Yii::getAlias('@data/StockOnHand.csv'));
        $count = count($csv);
        foreach ($csv as $k => $row) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout($row['ProductCode'] . ' - ');

            $component = Component::find()->notDeleted()->andWhere(['code' => $row['ProductCode']])->one();
            if (!$component) {
                $this->stdout('NOT FOUND!' . "\n");
                continue;
            }
            $component->track_stock = 1;
            if (!$component->save()) {
                debug($component->errors);
                die;
            }
            GearmanManager::runDearPush(DearProduct::className(), $component->id);
            sleep(5);
            $this->stdout("\n");
        }

        $this->stdout('DONE!' . "\n");
        return self::EXIT_CODE_NORMAL;
    }

}