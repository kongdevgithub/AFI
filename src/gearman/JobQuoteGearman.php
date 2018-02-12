<?php

namespace app\gearman;

use app\components\GearmanManager;
use app\components\quotes\jobs\BaseJobQuote;
use app\models\HubSpotDeal;
use app\models\Job;
use app\models\Log;
use Yii;

/**
 * QuoteJob
 */
class JobQuoteGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo $params['id'];
        $model = Job::findOne($params['id']);
        if (!$model) {
            echo 'not found, sleeping... ';
            sleep(5);
            $model = Job::findOne($params['id']);
            if (!$model) {
                echo 'skipping...';
                return;
            }
        }
        if ($model->deleted_at) {
            echo 'deleted, skipping...';
            return;
        }

        echo ' - ' . $model->name . ' - ';
        Log::log('quote generating', $model);

        //if ($model->quote_generated != 0) {
        //    Log::log('quote generation not needed, skipping...', $model);
        //    echo 'quote generation not needed, skipping...';
        //    return;
        //}
        //if ($model->status != 'job/draft') {
        //    Log::log('quote not in draft, skipping...', $model);
        //    echo 'quote not in draft, skipping...';
        //    return;
        //}

        // lock job
        $mutexKey = 'JobQuoteGearman.' . $model->id;
        while (!Yii::$app->mutex->acquire($mutexKey)) {
            Log::log('no lock on ' . $mutexKey . ' - sleeping...', $model);
            sleep(1);
        }

        if (YII_ENV_PROD)
            ob_start();

        /** @var BaseJobQuote $jobQuote */
        $model->refresh();
        $jobQuote = new $model->quote_class;
        $jobQuote->saveQuote($model, true);

        // release lock
        Yii::$app->mutex->release($mutexKey);

        // done
        if (YII_ENV_PROD) {
            $output = ob_get_clean();
            Log::log($output . ' - done', $model);
            echo $output . ' - done!';
        } else {
            echo ' - done!';
        }

        // push the deal back to hubspot
        GearmanManager::runHubSpotPush(HubSpotDeal::className(), $model->id);
    }

}