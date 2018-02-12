<?php

namespace app\models\workflow;

use raoul2000\workflow\events\WorkflowEvent;
use yii\base\Object;

/**
 * BaseWorkflow
 * Automatically calls the before/after enter/leave functions after status change occurs.
 *
 *
 * Workflow notes:
 *
 * the model is entering into the workflow:
 * ```
 * if ($event->getStartStatus() == null && $event->getEndStatus() != null) {}
 * ```
 *
 * the model is changing status
 * ```
 * if ($event->getStartStatus() != null && $event->getEndStatus() != null) {}
 * ```
 *
 * the model is leaving into the workflow
 * ```
 * if ($event->getStartStatus() == null && $event->getEndStatus() == null) {}
 * ```
 *
 * @package app\models\workflow
 */
abstract class BaseWorkflow extends Object
{

    /**
     * Automatically calls beforeEnter_{status} and beforeLeave_{status} methods if they exist.
     * Called by a model event trigger before a status change
     *
     * @param WorkflowEvent $event
     */
    public static function beforeChangeStatus($event)
    {
        $model = $event->sender->owner;
        $class = static::className();
        // beforeEnter
        if ($event->getEndStatus() != null) {
            $method = 'beforeEnter_' . explode('/', $event->getEndStatus()->getId())[1];
            if (method_exists($class, $method)) {
                call_user_func_array([$class, $method], ['model' => $model, 'event' => $event]);
            }
        }
        // beforeLeave
        if ($event->getStartStatus() != null) {
            $method = 'beforeLeave_' . explode('/', $event->getStartStatus()->getId())[1];
            if (method_exists($class, $method)) {
                call_user_func_array([$class, $method], ['model' => $model, 'event' => $event]);
            }
        }
    }

    /**
     * Automatically calls afterEnter_{status} and afterLeave_{status} methods if they exist
     * Called by a model event trigger after a status change
     *
     * @param WorkflowEvent $event
     */
    public static function afterChangeStatus($event)
    {
        $model = $event->sender->owner;
        $class = static::className();
        // afterEnter
        if ($event->getEndStatus() != null) {
            $method = 'afterEnter_' . explode('/', $event->getEndStatus()->getId())[1];
            if (method_exists($class, $method)) {
                call_user_func_array([$class, $method], ['model' => $model, 'event' => $event]);
            }
        }
        // afterLeave
        if ($event->getStartStatus() != null) {
            $method = 'afterLeave_' . explode('/', $event->getStartStatus()->getId())[1];
            if (method_exists($class, $method)) {
                call_user_func_array([$class, $method], ['model' => $model, 'event' => $event]);
            }
        }
    }
}