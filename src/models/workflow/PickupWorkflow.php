<?php

namespace app\models\workflow;

use app\components\EmailManager;
use app\components\GearmanManager;
use app\models\Pickup;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;

/**
 * PickupWorkflow
 * @package app\models\workflow
 */
class PickupWorkflow extends BaseWorkflow
{
    /**
     * @param Pickup $pickup
     * @param WorkflowEvent $event
     */
    public static function afterEnter_collected($pickup, $event)
    {
        /** @see PickupWorkflowAfterEnterCollectedGearman */
        GearmanManager::runPickupWorkflow_afterEnter_collected($pickup->id);
    }

    /**
     * @param Pickup $pickup
     * @param WorkflowEvent $event
     */
    public static function afterLeave_collected($pickup, $event)
    {
        /** @see PickupWorkflowAfterLeaveCollectedGearman */
        GearmanManager::runPickupWorkflow_afterLeave_collected($pickup->id);
    }
}