<?php

namespace app\gearman;

use app\components\GearmanManager;
use app\models\HubSpotCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use Yii;

/**
 * HubSpotPushGearman
 */
class HubSpotPushGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        $class = $params['class'];
        $id = $params['id'];
        echo $class . '-' . $id;

        /** @var HubSpotCompany|HubSpotContact|HubSpotDeal $class */
        $_POST['response'] = $class::hubSpotPush($id);
        sleep(2); // give the API a break
    }
}