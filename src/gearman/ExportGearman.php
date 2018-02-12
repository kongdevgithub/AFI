<?php

namespace app\gearman;

use app\components\CsvManager;
use app\models\Export;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\models\Product;
use app\models\query\ItemQuery;
use app\models\query\JobQuery;
use app\models\query\ProductQuery;
use app\models\search\ComponentSearch;
use app\models\search\ItemSearch;
use app\models\search\JobSearch;
use app\models\search\ProductSearch;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * ExportGearman
 */
class ExportGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo $params['id'];
        $export = Export::findOne($params['id']);
        if (!$export) {
            echo 'not found, skipping...';
            return;
        }
        if ($export->status == 'complete') {
            echo 'already done, skipping...';
            return;
        }

        // lock export
        $mutexKey = 'ExportGearman.' . $export->id;
        while (!Yii::$app->mutex->acquire($mutexKey)) {
            Log::log('no lock on ' . $mutexKey . ' - sleeping...', $export);
            sleep(1);
        }


        // process export
        $export->status = 'processing';
        $export->save(false);

        $filename = Yii::$app->runtimePath . '/export/' . $export->id . '.csv';
        FileHelper::createDirectory(dirname($filename));
        if (file_exists($filename)) {
            unlink($filename);
        }

        /** @var ItemSearch|ProductSearch|JobSearch|ComponentSearch $searchModel */
        $searchModel = new $export->model_name;
        $dataProvider = $searchModel->search([
            array_pop(explode('\\', $export->model_name)) => Json::decode($export->model_params),
        ]);

        // set total rows
        $export->total_rows = $dataProvider->getTotalCount();
        $export->save(false);

        /** @var ItemQuery|ProductQuery|JobQuery $query */
        $query = $dataProvider->query;

        // build csv
        $fp = fopen($filename, 'w');
        $header = false;
        foreach ($query->each(100) as $k => $model) {
            /** @var Item|Product|Job $model */
            $fields = CsvManager::csvRow($model);
            if (!$header) {
                fputcsv($fp, array_keys($fields));
                $header = true;
            }
            fputcsv($fp, $fields);
            echo '.';
        }
        fclose($fp);

        // upload to s3
        $remoteFilename = implode('/', [
            'export',
            md5(implode(',', [$export->id, $export->created_at, '5IPt6Pm7I5n81lw'])), // secret hash
            array_pop(explode('\\', $export->model_name)) . '_' . $export->id . '_' . date('Ymdhis', $export->created_at) . '.csv',
        ]);
        Yii::$app->s3->upload($remoteFilename, $filename);

        gc_collect_cycles(); // https://stackoverflow.com/a/41251821/599477
        unlink($filename);

        // save export
        $export->status = 'complete';
        $export->total_rows = $dataProvider->getTotalCount();
        $export->remote_filename = $remoteFilename;
        $export->save(false);

        // release lock
        Yii::$app->mutex->release($mutexKey);

        echo ' - done!';
    }


}