<?php

namespace app\models\workflow;

use app\components\GearmanManager;
use app\models\Package;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;

/**
 * PackageWorkflow
 * @package app\models\workflow
 */
class PackageWorkflow extends BaseWorkflow
{

    /**
     * @param Package $package
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_packing($package, $event)
    {
        // check if pickup is collected
        if ($package->pickup && $package->pickup->status == 'pickup/collected') {
            $event->invalidate(Yii::t('app', 'The pickup is collected.'));
            return;
        }
    }

    /**
     * @param Package $package
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_collected($package, $event)
    {
        // check if pickup is collected
        if (!$package->pickup || $package->pickup->status != 'pickup/collected') {
            $event->invalidate(Yii::t('app', 'The pickup is not collected.'));
            return;
        }
    }

    /**
     * @param Package $package
     * @param WorkflowEvent $event
     */
    public static function afterEnter_collected($package, $event)
    {
        /** @see PackageWorkflowAfterEnterCollectedGearman */
        GearmanManager::runPackageWorkflow_afterEnter_collected($package->id);
    }

    /**
     * @param Package $package
     * @param WorkflowEvent $event
     */
    public static function afterLeave_collected($package, $event)
    {
        /** @see PackageWorkflowAfterLeaveCollectedGearman */
        GearmanManager::runPackageWorkflow_afterLeave_collected($package->id);
    }
}