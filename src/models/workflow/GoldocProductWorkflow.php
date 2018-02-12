<?php

namespace app\models\workflow;

use app\components\GearmanManager;
use app\modules\goldoc\components\AfiExportHelper;
use app\modules\goldoc\models\Product;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;


/**
 * ProductWorkflow
 * @package app\models\workflow
 */
class GoldocProductWorkflow extends BaseWorkflow
{

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function beforeLeave_budgetApproval($product, $event)
    {
        if (!Yii::$app->user->can('goldoc-goldoc-admin')) {
            $message = Yii::t('app', 'This action requires role "goldoc-goldoc-admin".');
            $event->invalidate($message);
        }
        return;
    }

    /**
     * @param Product $product
     * @param WorkflowEvent $event
     */
    public static function afterEnter_production($product, $event)
    {
        GearmanManager::runGoldocProductWorkflow_afterEnter_production($product->id);
    }

}