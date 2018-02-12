<?php

namespace app\gearman;

use app\components\GearmanManager;
use app\models\DearProduct;
use app\models\DearSale;
use Yii;

/**
 * DearPushGearman
 */
class DearPushGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        $class = $params['class'];
        $id = $params['id'];
        $force = $params['force'];
        echo $class . '-' . $id;

        /** @var DearProduct|DearSale $class */
        $_POST['response'] = $class::dearPush($id, $force);
        if ($_POST['response']) {
            sleep(2); // give the API a break
        }
    }
}