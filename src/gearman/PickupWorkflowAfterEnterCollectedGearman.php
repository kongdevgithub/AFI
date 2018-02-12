<?php

namespace app\gearman;

use app\components\GearmanManager;
use app\components\Helper;
use app\components\quotes\jobs\BaseJobQuote;
use app\models\HubSpotDeal;
use app\models\Job;
use app\models\Log;
use app\models\Pickup;
use Yii;
use yii\base\Exception;

/**
 * PickupWorkflowAfterEnterCollectedGearman
 */
class PickupWorkflowAfterEnterCollectedGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo 'pickup-' . $params['id'];
        $pickup = Pickup::findOne($params['id']);

        // wait for pickup to be in collected status
        for ($i = 0; $i < 60; $i++) {
            if ($pickup->status == 'pickup/collected') {
                break;
            }
            echo ' - sleeping';
            sleep(1);
            $pickup->refresh();
        }

        // move packing packages to collected
        foreach ($pickup->getPackages()->andWhere(['status' => 'package/packing'])->all() as $package) {
            /** @var \app\models\Package $package */
            echo ' - package-' . $package->id;
            $package->status = 'package/collected';
            if (!$package->save(false)) {
                throw new Exception('cannot save package-' . $package->id . ': ' . Helper::getErrorString($package));
            }
        }
    }

}