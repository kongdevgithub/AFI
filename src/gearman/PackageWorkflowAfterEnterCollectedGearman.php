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
 * PackageWorkflowAfterEnterCollectedGearman
 */
class PackageWorkflowAfterEnterCollectedGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo 'package-' . $params['id'];
        $package = Package::findOne($params['id']);

        // wait for package to be in collected status
        for ($i = 0; $i < 60; $i++) {
            if ($package->status == 'package/collected') {
                break;
            }
            echo ' - sleeping';
            sleep(1);
            $package->refresh();
        }

        // move units to complete
        foreach ($package->getUnits()->andWhere('status LIKE :packed', [':packed' => '%/packed'])->all() as $unit) {
            /** @var \app\models\Unit $unit */
            echo ' - unit-' . $unit->id;
            $unit->status = explode('/', $unit->status)[0] . '/complete';
            if (!$unit->save(false)) {
                throw new Exception('cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
            }
        }
    }

}