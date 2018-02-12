<?php

namespace app\models\workflow;

use app\components\EmailManager;
use app\components\GearmanManager;
use app\components\Helper;
use app\models\DearSale;
use app\models\Job;
use app\models\Product;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;
use yii\helpers\Html;

/**
 * JobWorkflow
 * @package app\models\workflow
 */
class JobWorkflow extends BaseWorkflow
{
    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_draft($job, $event)
    {
        // cannot move back to draft
        if (!$job->isNewRecord && !Yii::$app->user->can('admin')) {
            $event->invalidate(Yii::t('app', 'You cannot move a Job back to Draft.'));
            return;
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     * @throws \raoul2000\workflow\base\WorkflowException
     */
    public static function afterEnter_draft($job, $event)
    {
        // move products to draft
        foreach ($job->products as $product) {
            $product->sendToStatus(null);
            $product->enterWorkflow('product');
            $product->save(false);
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeLeave_draft($job, $event)
    {
        // check quote generated
        if ($job->quote_generated != 1) {
            $event->invalidate(Yii::t('app', 'Quote has not yet been generated.  Please wait until the generation completes.'));
            return;
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterLeave_draft($job, $event)
    {
        // send price alert email
        if (!$job->checkPriceMargin() && !$job->checkProductsPriceMargins()) {
            EmailManager::sendJobPriceAlert($job);
        }
        // send client quote alert
        if (in_array('client', $job->staffCsr->getRoles())) {
            EmailManager::sendClientQuoteAlert($job);
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_quote($job, $event)
    {
        //// check if is a shipping address
        //if (!$job->getShippingAddresses()->count()) {
        //    $event->invalidate(Yii::t('app', 'There is no shipping address.'));
        //    return;
        //}
        // check quote_generated
        if ($job->quote_generated != 1) {
            $event->invalidate(Yii::t('app', 'Quote has not been generated, please wait for the generation to complete!'));
            return;
        }
        // check totals
        if (!$job->checkTotals()) {
            $event->invalidate(Yii::t('app', 'Quote totals are incorrect, please regenerate quote!'));
            return;
        }
        // check discount
        if (!$job->allow_excessive_discount) {
            if ($discount = $job->getExcessiveDiscount()) {
                $event->invalidate(Yii::t('app', 'Discount of {discount} is more than the maximum of {maximum}.', [
                    'discount' => number_format($discount, 2),
                    'maximum' => number_format($job->quote_maximum_discount_price * 1.2, 2),
                ]));
                return;
            }
        }
        // check if there are products
        if (!$job->getProducts()->count()) {
            $event->invalidate(Yii::t('app', 'There are no products.'));
            return;
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterEnter_quote($job, $event)
    {
        // send email
        if ($job->send_email) {
            EmailManager::sendQuoteApproval($job);
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_productionPending($job, $event)
    {
        // check if there are products
        if (!$job->getProducts()->count()) {
            $event->invalidate(Yii::t('app', 'There are no products.'));
            return;
        }

        // check fork quantities
        $messages = [];
        foreach ($job->products as $product) {
            if ($product->getForkQuantityProducts()->count()) {
                $messages[] = Html::a('product-' . $product->id, ['/product/view', 'id' => $product->id], ['target' => '_blank']) . ' - ' . $product->name;
            }
        }
        if ($messages) {
            $message = Yii::t('app', 'The following products have forked quantities: {products}', ['products' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }

    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_production($job, $event)
    {
        //// check if is a shipping address
        //if (!$job->getShippingAddresses()->count()) {
        //    $event->invalidate(Yii::t('app', 'There is no shipping address.'));
        //    return;
        //}

        // check staff permissions
        if (!Yii::$app->user->can('staff')) {
            $event->invalidate(Yii::t('app', 'Only staff can progress to Production.'));
            return;
        }

        // check date
        if (!$job->allow_early_due) {
            if (strtotime($job->due_date) < strtotime(Helper::getRelativeDate(date('Y-m-d'), 3))) {
                $event->invalidate(Yii::t('app', 'Job is due too soon.'));
                return;
            }
        }

        // check if any products are not complete
        if (!$job->getProducts()->andWhere(['not in', 'status', ['product/despatch', 'product/complete']])->count()) {
            $event->invalidate(Yii::t('app', 'All products are complete.'));
            return;
        }

        // check if company is suspended
        if ($job->company->status == 'company/suspended') {
            $event->invalidate(Yii::t('app', 'The company {company} is suspended.', [
                'company' => Html::a($job->company->name, ['company/view', 'id' => $job->company->id]),
            ]));
            return;
        }

        // check discount
        if (!$job->allow_excessive_discount) {
            $discount = $job->getExcessiveDiscount();
            if ($discount > 0) {
                $event->invalidate(Yii::t('app', 'Discount and Preserved Prices of {discount} is more than the maximum of {maximum}.', [
                    'discount' => number_format($discount, 2),
                    'maximum' => number_format($job->quote_maximum_discount_price * 1.2, 2),
                ]));
                return;
            }
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterEnter_production($job, $event)
    {
        // move products to next status (production)
        /** @var Product[] $products */
        $products = $job->getProducts()->andWhere(['status' => 'product/draft'])->all();
        foreach ($products as $product) {
            $product->status = $product->getNextStatus();
            $product->save(false);
        }

        // upload to dear
        GearmanManager::runDearPush(DearSale::className(), $job->id);
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_despatch($job, $event)
    {
        // only check if we are not in prebuild
        if ($event->getStartStatus()->getId() != 'job/prebuild') {
            // check if any products are not complete
            /** @var Product[] $products */
            $products = $job->getProducts()
                ->andWhere('quantity > 0')
                ->andWhere('status NOT LIKE :despatch AND status NOT LIKE :packed AND status NOT LIKE :complete', [
                    ':despatch' => '%/despatch',
                    ':packed' => '%/packed',
                    ':complete' => '%/complete',
                ])
                ->all();
            foreach ($products as $k => $product) {
                if ($product->isVirtual()) {
                    unset($products[$k]);
                }
            }
            if ($products) {
                $messages = [];
                foreach ($products as $product) {
                    $messages[] = Html::a('product-' . $product->id, ['/product/view', 'id' => $product->id], ['target' => '_blank']) . ' - ' . $product->name;
                }
                $message = Yii::t('app', 'The following products are not despatch/packed/complete: {products}', ['products' => Html::ul($messages, ['encode' => false])]);
                $event->invalidate($message);
                return;
            }
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_packed($job, $event)
    {
        // check if any products are not complete
        /** @var Product[] $products */
        $products = $job->getProducts()
            ->andWhere('quantity > 0')
            ->andWhere('status NOT LIKE :packed AND status NOT LIKE :complete', [':packed' => '%/packed', ':complete' => '%/complete'])
            ->all();
        foreach ($products as $k => $product) {
            if ($product->isVirtual()) {
                unset($products[$k]);
            }
        }
        if ($products) {
            $messages = [];
            foreach ($products as $product) {
                $messages[] = Html::a('product-' . $product->id, ['/product/view', 'id' => $product->id], ['target' => '_blank']) . ' - ' . $product->name;
            }
            $message = Yii::t('app', 'The following products are not packed/complete: {products}', ['products' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function beforeEnter_complete($job, $event)
    {
        // check if any products are not complete
        /** @var Product[] $products */
        $products = $job->getProducts()->andWhere(['!=', 'status', 'product/complete'])->all();
        foreach ($products as $k => $product) {
            if ($product->isVirtual()) {
                unset($products[$k]);
            }
        }
        if ($products) {
            $messages = [];
            foreach ($products as $product) {
                $messages[] = Html::a('product-' . $product->id, ['/product/view', 'id' => $product->id], ['target' => '_blank']) . ' - ' . $product->name;
            }
            $message = Yii::t('app', 'The following products are not complete: {products}', ['products' => Html::ul($messages, ['encode' => false])]);
            $event->invalidate($message);
            return;
        }
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterEnter_complete($job, $event)
    {
        // upload to dear
        GearmanManager::runDearPush(DearSale::className(), $job->id);
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterEnter_suspended($job, $event)
    {
        // send email
        EmailManager::sendJobSuspendedAlert($job);
    }

    /**
     * @param Job $job
     * @param WorkflowEvent $event
     */
    public static function afterEnter_cancelled($job, $event)
    {
        // send email
        if (in_array($event->getStartStatus()->getId(), ['job/production', 'job/despatch', 'job/packed', 'job/complete'])) {
            EmailManager::sendJobCancelledAlert($job);
        }
        // upload to dear
        GearmanManager::runDearPush(DearSale::className(), $job->id);
    }

}