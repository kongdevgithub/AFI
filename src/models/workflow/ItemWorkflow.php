<?php

namespace app\models\workflow;

use app\components\EmailManager;
use app\models\Component;
use app\models\Item;
use app\models\ItemType;
use app\models\Option;
use app\models\Unit;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * ItemWorkflow
 * @package app\models\workflow
 */
class ItemWorkflow extends BaseWorkflow
{

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_draft($item, $event)
    {
        // move units to draft
        /** @var Unit[] $units */
        $units = $item->getUnits()
            ->andWhere('status NOT LIKE :qualityFail', [':qualityFail' => '%/qualityFail'])
            ->all();
        foreach ($units as $unit) {
            $unit->sendToStatus(null);
            $unit->enterWorkflow('unit');
            $unit->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_awaitingInfo($item, $event)
    {
        // only allow printing if it is required
        if ($item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_design($item, $event)
    {
        // only allow design if it has a design component
        // TODO
        $message = Yii::t('app', 'Not implemented.');
        $event->invalidate($message);
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_artwork($item, $event)
    {
        // only allow printing if it is required
        if ($item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeLeave_artwork($item, $event)
    {
        //if ($event->getEndStatus()) {
        //    $endStatus = explode('/', $event->getEndStatus()->getId())[1];
        //    if (in_array($endStatus, ['approval'])) {
        //        if ($item->item_type_id == ItemType::ITEM_TYPE_PRINT && !$item->artwork) {
        //            $event->invalidate(Yii::t('app', 'The item does not have artwork.'));
        //        }
        //    }
        //}
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_change($item, $event)
    {
        // only allow printing if it is required
        if ($item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_approval($item, $event)
    {
        // only allow printing if it is required
        if ($item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_approval($item, $event)
    {
        // send email
        if ($item->send_email) {
            EmailManager::sendArtworkApproval($item->product->job);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_rip($item, $event)
    {
        // only allow if product is in production
        if ($item->product && $item->product->status != 'product/production') {
            $event->invalidate(Yii::t('app', 'The product is not in production.'));
        }
        // only allow printing if it is required
        if ($item->isBlankPrint()) {
            $message = Yii::t('app', 'Printing is not required on this Item.');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_production($item, $event)
    {
        // only allow if product is in production
        if ($item->product && !in_array($item->product->status, ['product/production', 'product/despatch', 'product/packed'])) {
            $event->invalidate(Yii::t('app', 'The product is not in production.'));
        }
        return;
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_production($item, $event)
    {
        // move units to production
        /** @var Unit[] $units */
        $units = $item->getUnits()
            ->andWhere('status LIKE :draft', [':draft' => '%/draft'])
            ->all();
        foreach ($units as $unit) {
            $unit->status = $unit->getNextStatus();
            $unit->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_prebuild($item, $event)
    {
        // only allow prebuild if it is required
        if ($item->product && !$item->product->prebuild_required) {
            $message = Yii::t('app', 'Prebuild is not required on this Product.');
            $event->invalidate($message);
            return;
        }
        // check if any units are not prebuild
        /** @var Unit[] $units */
        $units = $item->getUnits()
            ->andWhere('status NOT LIKE :qualityFail', [':qualityFail' => '%/qualityFail'])
            ->andWhere('status NOT LIKE :despatch', [':despatch' => '%/despatch'])
            ->andWhere('status NOT LIKE :packed', [':packed' => '%/packed'])
            ->andWhere('status NOT LIKE :prebuild', [':prebuild' => '%/prebuild'])
            ->all();
        if ($units) {
            $messages = [];
            foreach ($units as $unit) {
                $messages[] = Html::a('unit-' . $unit->id, ['/item/view', 'id' => $unit->item->id], ['target' => '_blank']) . ' - ' . $unit->item->name;
            }
            $message = Yii::t('app', 'The following units are not prebuild or despatch: {units}', ['units' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_prebuild($item, $event)
    {
        // move product to prebuild
        if ($item->product->isValidNextStatus('product/prebuild')) {
            $item->product->status = 'product/prebuild';
            $item->product->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterLeave_prebuild($item, $event)
    {
        // move product to production
        if ($event->getEndStatus() && !in_array(explode('/', $event->getEndStatus()->getId())[1], ['despatch', 'packed', 'complete'])) {
            $item->product->status = 'product/production';
            $item->product->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_despatch($item, $event)
    {
        // only check if we are not in prebuild
        if (explode('/', $event->getStartStatus()->getId())[1] != 'prebuild') {
            // check if any units are not complete or complete
            /** @var Unit[] $units */
            $units = $item->getUnits()
                ->andWhere('status NOT LIKE :qualityFail', [':qualityFail' => '%/qualityFail'])
                ->andWhere('status NOT LIKE :despatch', [':despatch' => '%/despatch'])
                ->andWhere('status NOT LIKE :packed', [':packed' => '%/packed'])
                ->andWhere('status NOT LIKE :complete', [':complete' => '%/complete'])
                ->all();
            if ($units) {
                $messages = [];
                foreach ($units as $unit) {
                    $messages[] = Html::a('unit-' . $unit->id, ['/item/view', 'id' => $unit->item->id], ['target' => '_blank']) . ' - ' . $unit->item->name;
                }
                $message = Yii::t('app', 'The following units are not despatch/packed/complete: {units}', ['units' => Html::ul($messages, ['encode' => false])]);
                $event->invalidate($message);
                return;
            }
        }
        //// do not allow if the product is in prebuild
        //if ($item->product && in_array($item->product->status, ['product/production', 'product/prebuild'])) {
        //    $message = Yii::t('app', 'The product has not passed prebuild.');
        //    $event->invalidate($message);
        //    return;
        //}
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_despatch($item, $event)
    {
        // moving from prebuild
        if (explode('/', $event->getStartStatus()->getId())[1] == 'prebuild') {
            // move units to despatch
            /** @var Unit[] $units */
            $units = $item->getUnits()
                ->andWhere('status LIKE :status', [':status' => '%/prebuild'])
                ->all();
            foreach ($units as $unit) {
                $unit->status = explode('/', $unit->status)[0] . '/despatch';
                $unit->save(false);
            }
        }
        // move product to despatch
        if ($item->product->status != 'product/despatch' && $item->product->isValidNextStatus('product/despatch')) {
            $item->product->status = 'product/despatch';
            $item->product->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterLeave_despatch($item, $event)
    {
        // move product to production
        if ($event->getEndStatus() && !in_array(explode('/', $event->getEndStatus()->getId())[1], ['packed', 'complete'])) {
            $item->product->status = 'product/production';
            $item->product->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_packed($item, $event)
    {
        // check if any units are not packed or complete
        /** @var Unit[] $units */
        $units = $item->getUnits()
            ->andWhere('status NOT LIKE :qualityFail', [':qualityFail' => '%/qualityFail'])
            ->andWhere('status NOT LIKE :packed', [':packed' => '%/packed'])
            ->andWhere('status NOT LIKE :complete', [':complete' => '%/complete'])
            ->all();
        if ($units) {
            $messages = [];
            foreach ($units as $unit) {
                $messages[] = Html::a('unit-' . $unit->id, ['/item/view', 'id' => $unit->item->id], ['target' => '_blank']) . ' - ' . $unit->item->name;
            }
            $message = Yii::t('app', 'The following units are not packed/complete: {units}', ['units' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_packed($item, $event)
    {
        // move product to packed
        if ($item->product->status != 'product/packed' && $item->product->isValidNextStatus('product/packed')) {
            $item->product->status = 'product/packed';
            $item->product->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterLeave_packed($item, $event)
    {
        // move product to despatch/production
        $endStatus = $event->getEndStatus();
        if ($endStatus) {
            $status = explode('/', $endStatus->getId())[1];
            if (!in_array($status, ['complete'])) {
                if ($status == 'despatch') {
                    $item->product->status = 'product/despatch';
                } else {
                    $item->product->status = 'product/production';
                }
                $item->product->save(false);
            }
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_complete($item, $event)
    {
        // check if any items are not complete
        /** @var Unit[] $units */
        $units = $item->getUnits()
            ->andWhere('status NOT LIKE :qualityFail', [':qualityFail' => '%/qualityFail'])
            ->andWhere('status NOT LIKE :complete', [':complete' => '%/complete'])
            ->all();
        if ($units) {
            $messages = [];
            foreach ($units as $unit) {
                $messages[] = Html::a('unit-' . $unit->id, ['/item/view', 'id' => $unit->item->id], ['target' => '_blank']) . ' - ' . $unit->item->name;
            }
            $message = Yii::t('app', 'The following units are not complete: {units}', ['units' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_complete($item, $event)
    {
        // move product to complete
        if ($item->product->isValidNextStatus('product/complete')) {
            $item->product->status = 'product/complete';
            $item->product->save(false);
        }
        // mark installation as complete
        if ($item->item_type_id == ItemType::ITEM_TYPE_INSTALLATION) {
            $item->product->job->installed_at = time();
            $item->product->job->save(false);
        }
    }

    /**
     * @param Item $item
     * @param WorkflowEvent $event
     */
    public static function afterEnter_hold($item, $event)
    {
        // send email
        EmailManager::sendItemHoldAlert($item);
    }

}