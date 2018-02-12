<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\EmailManager;
use app\components\Helper;
use app\models\Pickup;
use yii\console\Controller;

/**
 * Class PickupController
 * @package app\commands
 */
class PickupController extends Controller
{


    /**
     *
     */
    public function actionScrapePods()
    {
        $this->stdout('FINDING PICKUPS' . "\n");
        $pickups = Pickup::find()
            ->joinWith(['carrier'])
            ->andWhere(['pickup.pod_date' => null])
            ->andWhere(['not', ['pickup.carrier_ref' => null]])
            ->andWhere(['not', ['pickup.carrier_ref' => '']])
            ->andWhere(['>=', 'pickup.created_at', strtotime('-30days')])
            ->andWhere(['not', ['carrier.tracking_url' => null]])
            ->andWhere(['not', ['carrier.tracking_url' => '']])
            ->notDeleted();
        $count = $pickups->count();

        foreach ($pickups->each(100) as $k => $pickup) {
            /** @var Pickup $pickup */
            $this->stdout(CommandStats::stats($k + 1, $count) . 'pickup-' . $pickup->id . ' ');
            $pod = $pickup->scrapePOD();
            if ($pod) {
                $this->stdout('FOUND POD - ');
                $pickup->pod_date = date('Y-m-d H:i:s', strtotime($pod));
                $this->stdout($pod . ' ' . $pickup->pod_date . ' ');
                if (!$pickup->save(false)) {
                    $this->stdout('cannot save pickup: ' . Helper::getErrorString($pickup));
                }
                EmailManager::sendPickupDelivered($pickup);
            } else {
                $this->stdout($pickup->getTrackingUrl());
            }
            $this->stdout("\n");
        }
        $this->stdout('DONE!' . "\n");
    }

}
