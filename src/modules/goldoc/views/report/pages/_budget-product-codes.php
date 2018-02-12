<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Substrate;
use yii\db\Query;
use yii\helpers\Html;

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('goldoc', 'Items'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th><?= Yii::t('goldoc', 'Code') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Qty') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Product') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Labour') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Machine') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Total') ?></th>
            </tr>
            </thead>
            <tbody>

            <?php
            // total
            $total = (new Query())
                ->select([
                    'SUM(quantity) as quantity_sum',
                    'SUM(product_price) as product_price_sum',
                    'SUM(labour_price) as labour_price_sum',
                    'SUM(machine_price) as machine_price_sum',
                    'SUM(total_price) as total_price_sum',
                ])
                ->from('product')
                ->andWhere([
                    'product.deleted_at' => null,
                    'product.status' => [
                        'goldoc-product/artworkApproval',
                        'goldoc-product/artworkUpload',
                        'goldoc-product/productionPending',
                        'goldoc-product/production',
                        'goldoc-product/warehouseMelbourne',
                        'goldoc-product/warehouseGoldCoast',
                        'goldoc-product/installation',
                        'goldoc-product/complete',
                    ],
                ])
                ->orderBy(['SUM(product_price)' => SORT_DESC])
                ->one(Yii::$app->dbGoldoc);
            ?>
            <tr>
                <td><?= Yii::t('goldoc', 'ALL') ?></td>
                <td class="text-right"><?= $total['quantity_sum'] ?></td>
                <td class="text-right"><?= number_format($total['product_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['labour_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['machine_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['total_price_sum'], 2) ?></td>
            </tr>
            <?php

            // product codes
            $totals = (new Query())
                ->select([
                    'item_id',
                    'colour_id',
                    'design_id',
                    'substrate_id',
                    'width',
                    'height',
                    'depth',
                    'SUM(quantity) as quantity_sum',
                    'SUM(product_price) as product_price_sum',
                    'SUM(labour_price) as labour_price_sum',
                    'SUM(machine_price) as machine_price_sum',
                    'SUM(total_price) as total_price_sum',
                ])
                ->from('product')
                ->andWhere([
                    'product.deleted_at' => null,
                    'product.status' => [
                        'goldoc-product/artworkApproval',
                        'goldoc-product/artworkUpload',
                        'goldoc-product/productionPending',
                        'goldoc-product/production',
                        'goldoc-product/warehouseMelbourne',
                        'goldoc-product/warehouseGoldCoast',
                        'goldoc-product/installation',
                        'goldoc-product/complete',
                    ],
                ])
                ->leftJoin('item', 'item_id=item.id')
                ->leftJoin('colour', 'colour_id=colour.id')
                ->leftJoin('design', 'design_id=design.id')
                ->leftJoin('substrate', 'substrate_id=substrate.id')
                ->orderBy([
                    'item.code' => SORT_ASC,
                    'colour.code' => SORT_ASC,
                    'design.code' => SORT_ASC,
                    'substrate.code' => SORT_ASC,
                ])
                ->groupBy([
                    'item_id',
                    //'colour_id',
                    //'design_id',
                    //'substrate_id',
                    //'width',
                    //'height',
                    //'depth',
                ])
                ->all(Yii::$app->dbGoldoc);

            foreach ($totals as $total) {
                $code = [];
                $code[] = ($total['item_id'] && $item = Item::findOne($total['item_id'])) ? $item->code : 'X';
                //$code[] = ($total['colour_id'] && $colour = Colour::findOne($total['colour_id'])) ? $colour->code : 'X';
                //$code[] = ($total['design_id'] && $design = Design::findOne($total['design_id'])) ? $design->code : 'X';
                //$code[] = ($total['substrate_id'] && $substrate = Substrate::findOne($total['substrate_id'])) ? $substrate->code : 'X';
                //if ($total['width']) {
                //    if ($total['height']) {
                //        if ($total['depth']) {
                //            $code[] = $total['width'] . 'x' . $total['height'] . 'x' . $total['depth'];
                //        } else {
                //            $code[] = $total['width'] . 'x' . $total['height'];
                //        }
                //    } else {
                //        $code[] = $total['width'];
                //    }
                //}
                ?>
                <tr>
                    <td><?= Html::a(implode('-', $code), ['product/index', 'ProductSearch' => [
                            'item_id' => $total['item_id'] ? $total['item_id'] : '-',
                            //'colour_id' => $total['colour_id'] ? $total['colour_id'] : '-',
                            //'design_id' => $total['design_id'] ? $total['design_id'] : '-',
                            //'substrate_id' => $total['substrate_id'] ? $total['substrate_id'] : '-',
                            //'width' => $total['width'] ? $total['width'] : '-',
                            //'height' => $total['height'] ? $total['height'] : '-',
                            //'depth' => $total['depth'] ? $total['depth'] : '-',
                        ]], [
                            //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                            //'data-toggle' => 'tooltip',
                        ]) ?></td>
                    <td class="text-right"><?= $total['quantity_sum'] ?></td>
                    <td class="text-right"><?= number_format($total['product_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($total['labour_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($total['machine_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($total['total_price_sum'], 2) ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
