<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\models\Venue;
use yii\db\Query;
use yii\helpers\Html;

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('goldoc', 'Venues'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th><?= Yii::t('goldoc', 'Code') ?></th>
                <th><?= Yii::t('goldoc', 'Name') ?></th>
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
                <td><?= Yii::t('goldoc', 'All Venues') ?></td>
                <td class="text-right"><?= $total['quantity_sum'] ?></td>
                <td class="text-right"><?= number_format($total['product_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['labour_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['machine_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($total['total_price_sum'], 2) ?></td>
            </tr>
            <?php

            // venues
            $totals = (new Query())
                ->select([
                    'venue_id',
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
                ->leftJoin('venue', 'venue_id=venue.id')
                ->groupBy(['venue_id'])
                ->orderBy([
                    'venue.code' => SORT_ASC,
                    //'SUM(product_price)' => SORT_DESC,
                ])
                ->all(Yii::$app->dbGoldoc);

            foreach ($totals as $total) {
                $venue = Venue::findOne($total['venue_id']);
                ?>
                <tr>
                    <td><?= $venue ? Html::a($venue->code, ['product/index', 'ProductSearch' => [
                            'venue_id' => $total['venue_id'] ? $total['venue_id'] : '-',
                        ]], [
                            //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                            //'data-toggle' => 'tooltip',
                        ]) : '-' ?></td>
                    <td><?= $venue ? $venue->name : Yii::t('goldoc', 'None Assigned') ?></td>
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
