<?php
namespace app\gearman;

use app\components\BulkQuoteHelper;

/**
 * BuildJob
 */
class JobBuildGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo $params['key'];
        BulkQuoteHelper::getJob('TEST: ' . $params['key'], $params['job']);
    }

}