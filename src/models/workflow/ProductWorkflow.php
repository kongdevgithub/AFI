<?php

namespace app\models\workflow;

use app\components\EmailManager;
use app\models\Item;
use app\models\Product;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * ProductWorkflow
 * @package app\models\workflow
 */
class ProductWorkflow extends BaseWorkflow
{

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_draft($product, $event)
    {
        // move items to draft
        foreach ($product->items as $item) {
            $item->sendToStatus(null);
            $item->enterWorkflow('item');
            $item->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_production($product, $event)
    {
        // only allow if product is in production
        if (!in_array($product->job->status, ['job/production', 'job/despatch'])) {
            $event->invalidate(Yii::t('app', 'The job is not in production.'));
        }
        return;
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_production($product, $event)
    {
        // move items to next status (production)
        /** @var Item[] $items */
        $items = $product->getItems()
            ->andWhere('quantity > 0')
            ->andWhere('status LIKE :status', [':status' => '%/draft'])
            ->all();
        foreach ($items as $item) {
            $item->status = $item->getNextStatus();
            $item->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_prebuild($product, $event)
    {
        // only allow prebuild if it is required
        if (!$product->prebuild_required) {
            $message = Yii::t('app', 'Prebuild is not required on this Product.');
            $event->invalidate($message);
            return;
        }
        // check if any items are not prebuild
        /** @var Item[] $items */
        $items = $product->getItems()
            ->andWhere('quantity > 0')
            ->andWhere('status NOT LIKE :prebuild AND status NOT LIKE :despatch', [':prebuild' => '%/prebuild', ':despatch' => '%/despatch'])
            ->all();
        foreach ($items as $k => $item) {
            if ($item->isVirtual()) {
                unset($items[$k]);
            }
        }
        if ($items) {
            $messages = [];
            foreach ($items as $item) {
                $messages[] = Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id], ['target' => '_blank']) . ' - ' . $item->name;
            }
            $message = Yii::t('app', 'The following items are not prebuild or despatch: {items}', ['items' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_despatch($product, $event)
    {
        // only check if we are not in prebuild
        if ($event->getStartStatus()->getId() != 'product/prebuild') {
            // check if any items are not complete
            /** @var Item[] $items */
            $items = $product->getItems()
                ->andWhere('quantity > 0')
                ->andWhere('status NOT LIKE :despatch AND status NOT LIKE :packed AND status NOT LIKE :complete', [':despatch' => '%/despatch', ':packed' => '%/packed', ':complete' => '%/complete'])
                ->all();
            foreach ($items as $k => $item) {
                if ($item->isVirtual()) {
                    unset($items[$k]);
                }
            }
            if ($items) {
                $messages = [];
                foreach ($items as $item) {
                    $messages[] = Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id], ['target' => '_blank']) . ' - ' . $item->name;
                }
                $message = Yii::t('app', 'The following items are not despatch/packed/complete: {items}', ['items' => Html::ul($messages, ['encode' => false])]);
                $event->invalidate($message);
                return;
            }
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_despatch($product, $event)
    {
        // move items to despatch
        if ($event->getStartStatus()->getId() == 'product/prebuild') {
            /** @var Item[] $items */
            $items = $product->getItems()
                ->andWhere('quantity > 0')
                ->andWhere('status LIKE :status', [':status' => '%/prebuild'])
                ->all();
            foreach ($items as $item) {
                $item->status = explode('/', $item->status)[0] . '/despatch';
                $item->save(false);
            }
        }

        // move job to despatch
        if ($product->job->status != 'job/despatch' && $product->job->isValidNextStatus('job/despatch')) {
            $product->job->status = 'job/despatch';
            $product->job->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterLeave_despatch($product, $event)
    {
        // move job to production
        if (!in_array($product->status, ['product/packed', 'product/complete'])) {
            $product->job->status = 'job/production';
            $product->job->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_packed($product, $event)
    {
        // check if any items are not complete
        /** @var Item[] $items */
        $items = $product->getItems()
            ->andWhere('quantity > 0')
            ->andWhere('status NOT LIKE :packed AND status NOT LIKE :complete', [':packed' => '%/packed', ':complete' => '%/complete'])
            ->all();
        foreach ($items as $k => $item) {
            if ($item->isVirtual()) {
                unset($items[$k]);
            }
        }
        if ($items) {
            $messages = [];
            foreach ($items as $item) {
                $messages[] = Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id], ['target' => '_blank']) . ' - ' . $item->name;
            }
            $message = Yii::t('app', 'The following items are not packed/complete: {items}', ['items' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_packed($product, $event)
    {
        // move job to packed
        if ($product->job->status != 'job/packed' && $product->job->isValidNextStatus('job/packed')) {
            $product->job->status = 'job/packed';
            $product->job->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterLeave_packed($product, $event)
    {
        // move job to despatch or production
        if ($product->status != 'product/complete') {
            if ($product->status == 'product/despatch') {
                $product->job->status = 'job/despatch';
            } else {
                $product->job->status = 'job/production';
            }
            $product->job->save(false);
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_complete($product, $event)
    {
        // check if any products are not complete
        /** @var Item[] $items */
        $items = $product->getItems()
            ->andWhere('quantity > 0')
            ->andWhere('status NOT LIKE :complete', [':complete' => '%/complete'])
            ->all();
        foreach ($items as $k => $item) {
            if ($item->isVirtual()) {
                unset($items[$k]);
            }
        }
        if ($items) {
            $messages = [];
            foreach ($items as $item) {
                $messages[] = Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id], ['target' => '_blank']) . ' - ' . $item->name;
            }
            $message = Yii::t('app', 'The following items are not complete: {items}', ['items' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_complete($product, $event)
    {
        // move job to complete
        if ($product->job->isValidNextStatus('job/complete')) {
            $product->job->status = 'job/complete';
            $product->job->save(false);
        }
    }
}