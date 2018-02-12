<?php

namespace app\gearman;

use app\components\GearmanManager;
use app\components\Helper;
use app\components\quotes\jobs\BaseJobQuote;
use app\models\HubSpotDeal;
use app\models\Job;
use app\models\Log;
use app\models\Package;
use Yii;
use yii\base\Exception;

/**
 * PackageWorkflowAfterLeaveCollectedGearman
 */
class PackageWorkflowAfterLeaveCollectedGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo 'package-' . $params['id'];
        $package = Package::findOne($params['id']);

        // wait for package to not be in collected status
        for ($i = 0; $i < 60; $i++) {
            if ($package->status != 'package/collected') {
                break;
            }
            echo ' - sleeping';
            sleep(1);
            $package->refresh();
        }

        // move units to packed
        foreach ($package->getUnits()->andWhere('status LIKE :complete', [':complete' => '%/complete'])->all() as $unit) {
            /** @var \app\models\Unit $unit */
            echo ' - unit-' . $unit->id;
            $unit->status = explode('/', $unit->status)[0] . '/packed';
            if (!$unit->save(false)) {
                throw new Exception('cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
            }
        }
    }

}