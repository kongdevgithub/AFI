<?php

namespace app\gearman;

use app\modules\goldoc\components\AfiExportHelper;
use app\modules\goldoc\models\Product;

/**
 * GoldocProductWorkflowAfterEnterProductionGearman
 */
class GoldocProductWorkflowAfterEnterProductionGearman extends BaseGearman
{
    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo 'goldoc-product-' . $params['id'];
        $product = Product::findOne($params['id']);
        AfiExportHelper::addProductToJob($product);
    }

}