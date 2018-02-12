<?php

namespace app\components;

use app\gearman\LogSlackGearman;
use shakura\yii2\gearman\Dispatcher;
use shakura\yii2\gearman\GearmanComponent;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\base\Component;

/**
 * GearmanManager
 */
class GearmanManager extends Component
{

    /**
     * @see JobQuoteGearman
     * @param int $id
     * @return bool
     */
    public static function runJobQuote($id)
    {
        return static::runBackground(Yii::$app->gearman, 'job-quote', ['id' => $id]);
    }

    /**
     * @see JobBuildGearman
     * @param string $key
     * @param array $job
     * @return string
     */
    public static function runJobBuild($key, $job)
    {
        return static::runBackground(Yii::$app->gearman, 'job-build', ['key' => $key, 'job' => $job]);
    }

    /**
     * @see JobProductImportGearman
     * @param int $id
     * @return string
     */
    public static function runJobProductImport($id)
    {
        return static::runBackground(Yii::$app->gearman, 'job-product-import', ['id' => $id]);
    }

    /**
     * @see HubSpotWebhookGearman
     * @param mixed $data
     * @param int $received_time
     * @return string
     */
    public static function runHubSpotWebhook($data, $received_time)
    {
        return static::runBackground(Yii::$app->gearmanHubSpot, 'hub-spot-webhook', ['data' => $data, 'received_time' => $received_time]);
    }

    /**
     * @see HubSpotPushGearman
     * @param string $class
     * @param int $id
     * @return string
     */
    public static function runHubSpotPush($class, $id)
    {
        return static::runBackground(Yii::$app->gearmanHubSpot, 'hub-spot-push', ['class' => $class, 'id' => $id], Dispatcher::LOW);
    }

    /**
     * @see DearPushGearman
     * @param string $class
     * @param int $id
     * @param bool $force
     * @return string
     */
    public static function runDearPush($class, $id, $force = false)
    {
        return static::runBackground(Yii::$app->gearmanDear, 'dear-push', ['class' => $class, 'id' => $id, 'force' => $force], Dispatcher::LOW);
    }

    /**
     * @see LogSlackGearman
     * @param int $log_id
     * @return string
     */
    public static function runLogSlack($log_id)
    {
        return false;
        return static::runBackground(Yii::$app->gearman, 'log-slack', ['id' => $log_id]);
    }

    /**
     * @see ExportGearman
     * @param int $id
     * @return bool
     */
    public static function runExport($id)
    {
        return static::runBackground(Yii::$app->gearmanExport, 'export', ['id' => $id], Dispatcher::LOW);
    }

    /**
     * @see PackageWorkflowAfterEnterCollectedGearman
     * @param int $id
     * @return bool
     */
    public static function runPackageWorkflow_afterEnter_collected($id)
    {
        return static::runBackground(Yii::$app->gearman, 'packageWorkflow-afterEnter-collected', ['id' => $id], Dispatcher::HIGH);
    }

    /**
     * @see PackageWorkflowAfterLeaveCollectedGearman
     * @param int $id
     * @return bool
     */
    public static function runPackageWorkflow_afterLeave_collected($id)
    {
        return static::runBackground(Yii::$app->gearman, 'packageWorkflow-afterLeave-collected', ['id' => $id], Dispatcher::HIGH);
    }

    /**
     * @see PickupWorkflowAfterEnterCollectedGearman
     * @param int $id
     * @return bool
     */
    public static function runPickupWorkflow_afterEnter_collected($id)
    {
        return static::runBackground(Yii::$app->gearman, 'pickupWorkflow-afterEnter-collected', ['id' => $id], Dispatcher::HIGH);
    }

    /**
     * @see PickupWorkflowAfterLeaveCollectedGearman
     * @param int $id
     * @return bool
     */
    public static function runPickupWorkflow_afterLeave_collected($id)
    {
        return static::runBackground(Yii::$app->gearman, 'pickupWorkflow-afterLeave-collected', ['id' => $id], Dispatcher::HIGH);
    }

    /**
     * @see PickupWorkflowAfterLeaveCollectedGearman
     * @param int $id
     * @return bool
     */
    public static function runGoldocProductWorkflow_afterEnter_production($id)
    {
        return static::runBackground(Yii::$app->gearman, 'goldocProductWorkflow-afterEnter-production', ['id' => $id], Dispatcher::HIGH);
    }

    /**
     * @param GearmanComponent $gearman
     * @param $task
     * @param $params
     * @param int $priority
     * @param $unique - another job will not start if one is already running
     * @return string
     */
    public static function runBackground($gearman, $task, $params, $priority = Dispatcher::NORMAL, $unique = null)
    {
        $name = getenv('APP_ID') . '-' . $task;
        $workload = ['params' => $params];
        return $gearman->getDispatcher()->background($name, new JobWorkload($workload), $priority, $unique);
    }

    /**
     * @param GearmanComponent $gearman
     * @param string $job_handle
     * @return array
     */
    public static function getBackgroundStatus($gearman, $job_handle)
    {
        return $gearman->getDispatcher()->getClient()->getClient()->jobStatus($job_handle);
    }

}