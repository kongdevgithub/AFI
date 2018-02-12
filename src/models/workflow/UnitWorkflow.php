<?php

namespace app\models\workflow;

use app\models\Component;
use app\models\ItemType;
use app\models\Option;
use app\models\Unit;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;

/**
 * UnitWorkflow
 * @package app\models\workflow
 */
class UnitWorkflow extends BaseWorkflow
{

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_fabrication($unit, $event)
    {
        // only allow for print items if fabrication is required
        if ($unit->item && $unit->item->item_type_id == ItemType::ITEM_TYPE_PRINT) {
            $fabricationRequired = false;
            $productToOption = $unit->item->getProductToOption(Option::OPTION_SUBSTRATE);
            if ($productToOption) {
                $component_id = $productToOption->valueDecoded;
                if ($component_id) {
                    $component = Component::findOne($component_id);
                    if ($component) {
                        $config = $component->getConfigDecoded();
                        if (!empty($config['fabrication_required'])) {
                            $fabricationRequired = true;
                        }
                    }
                }
            }
            if (!$fabricationRequired) {
                $message = Yii::t('app', 'Fabrication is not required on this Item.');
                $event->invalidate($message);
            }
        }
        return;
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_printing($unit, $event)
    {
        // only allow printing if it is required
        if ($unit->item && $unit->item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_prebuild($unit, $event)
    {
        // only allow prebuild if it is required
        if ($unit->item && !$unit->item->product->job->prebuild_required) {
            $message = Yii::t('app', 'Prebuild is not required on this Job.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterEnter_prebuild($unit, $event)
    {
        // move item to prebuild
        list($workflow, $status) = explode('/', $unit->item->status);
        if ($unit->item->isValidNextStatus($workflow . '/prebuild')) {
            $unit->item->status = $workflow . '/prebuild';
            $unit->item->save(false);
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterLeave_prebuild($unit, $event)
    {
        // move item to production
        if ($event->getEndStatus() && !in_array(explode('/', $event->getEndStatus()->getId())[1], ['despatch', 'packed', 'complete'])) {
            $unit->item->status = explode('/', $unit->item->status)[0] . '/production';
            $unit->item->save(false);
        }
    }

    ///**
    // * @param Unit $unit
    // * @param WorkflowEvent $event
    // */
    //public static function beforeEnter_despatch($unit, $event)
    //{
    //    // do not allow if the item is in prebuild
    //    if ($unit->item && explode('/', $unit->item->status)[1] == 'prebuild') {
    //        $message = Yii::t('app', 'The item is still in prebuild.');
    //        $event->invalidate($message);
    //        return;
    //    }
    //}

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterEnter_despatch($unit, $event)
    {
        // move item to despatch
        list($workflow, $status) = explode('/', $unit->item->status);
        if ($unit->item->isValidNextStatus($workflow . '/despatch')) {
            $unit->item->status = $workflow . '/despatch';
            $unit->item->save(false);
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterLeave_despatch($unit, $event)
    {
        // move item to production
        if ($event->getEndStatus() && !in_array(explode('/', $event->getEndStatus()->getId())[1], ['complete', 'packed'])) {
            $unit->item->status = explode('/', $unit->item->status)[0] . '/production';
            $unit->item->save(false);
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterEnter_packed($unit, $event)
    {
        // move item to packed
        list($workflow, $status) = explode('/', $unit->item->status);
        if ($unit->item->isValidNextStatus($workflow . '/packed')) {
            $unit->item->status = $workflow . '/packed';
            $unit->item->save(false);
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterLeave_packed($unit, $event)
    {
        // move item to production
        $endStatus = $event->getEndStatus();
        if ($endStatus) {
            $status = explode('/', $event->getEndStatus()->getId())[1];
            if (!in_array($status, ['complete'])) {
                if ($status == 'despatch') {
                    $unit->item->status = explode('/', $unit->item->status)[0] . '/despatch';
                } else {
                    $unit->item->status = explode('/', $unit->item->status)[0] . '/production';
                }
                $unit->item->save(false);
            }
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_complete($unit, $event)
    {
        if ($unit->item && !$unit->item->itemType->virtual) {
            // do not allow if the package is not collected
            if (!$unit->package || $unit->package->status != 'package/collected') {
                $message = Yii::t('app', 'The package is not collected.');
                $event->invalidate($message);
                return;
            }
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterEnter_complete($unit, $event)
    {
        // move item to complete
        list($workflow, $status) = explode('/', $unit->item->status);
        if ($unit->item->isValidNextStatus($workflow . '/complete')) {
            $unit->item->status = $workflow . '/complete';
            $unit->item->save(false);
        }
    }

    /**
     * @param Unit $unit
     * @param WorkflowEvent $event
     */
    public static function afterLeave_complete($unit, $event)
    {
        // move item to despatch or production
        list($workflow, $status) = explode('/', $unit->item->status);
        if (!$unit->item->itemType->virtual) {
            $unit->item->status = $workflow . '/despatch';
        } else {
            $unit->item->status = $workflow . '/production';
        }
        $unit->item->save(false);
    }

}