<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Venue;
use yii\db\Query;
use yii\helpers\Html;

if ($status == 'unapproved') {
    $statusList = [
        //'goldoc-product/draft',
        'goldoc-product/siteCheck',
        'goldoc-product/quotePending',
        'goldoc-product/budgetApproval',
    ];
} elseif ($status == 'approved') {
    $statusList = [
        'goldoc-product/artworkApproval',
        'goldoc-product/artworkUpload',
        'goldoc-product/productionPending',
        'goldoc-product/production',
        'goldoc-product/warehouseMelbourne',
        'goldoc-product/warehouseGoldCoast',
        'goldoc-product/installation',
        'goldoc-product/complete',
    ];
} else {
    $statusList = [
        //'goldoc-product/draft',
        'goldoc-product/siteCheck',
        'goldoc-product/quotePending',
        'goldoc-product/budgetApproval',
        'goldoc-product/artworkApproval',
        'goldoc-product/artworkUpload',
        'goldoc-product/productionPending',
        'goldoc-product/production',
        'goldoc-product/warehouseMelbourne',
        'goldoc-product/warehouseGoldCoast',
        'goldoc-product/installation',
        'goldoc-product/complete',
    ];
}
?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('goldoc', 'Venue Item Colour'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th><?= Yii::t('goldoc', 'Venue') ?></th>
                <th><?= Yii::t('goldoc', 'Item') ?></th>
                <th><?= Yii::t('goldoc', 'Colour') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Qty') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Product') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Labour') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Machine') ?></th>
                <th class="text-right"><?= Yii::t('goldoc', 'Total') ?></th>
            </tr>
            </thead>
            <tbody>


            <?php
            // all
            $totals_all = (new Query())
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
                    'product.status' => $statusList,
                ])
                ->one(Yii::$app->dbGoldoc);
            ?>
            <tr style="background: #dddddd">
                <td><?= 'ALL' ?></td>
                <td></td>
                <td></td>
                <td class="text-right"><?= $totals_all['quantity_sum'] ?></td>
                <td class="text-right"><?= number_format($totals_all['product_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($totals_all['labour_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($totals_all['machine_price_sum'], 2) ?></td>
                <td class="text-right"><?= number_format($totals_all['total_price_sum'], 2) ?></td>
            </tr>
            <?php


            // venues
            $totals_venues = (new Query())
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
                    'product.status' => $statusList,
                ])
                ->groupBy(['venue_id'])
                ->leftJoin('venue', 'venue_id=venue.id')
                ->orderBy([
                    'venue.code' => SORT_ASC,
                ])
                ->all(Yii::$app->dbGoldoc);

            foreach ($totals_venues as $totals_venue) {
                $venue = Venue::findOne($totals_venue['venue_id']);
                ?>
                <tr style="background: #dddddd">
                    <td><?= $venue ? Html::a($venue->code, ['product/index', 'ProductSearch' => [
                            'venue_id' => $totals_venue['venue_id'] ? $totals_venue['venue_id'] : '-',
                        ]], [
                            //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                            //'data-toggle' => 'tooltip',
                        ]) : '-' ?></td>
                    <td></td>
                    <td></td>
                    <td class="text-right"><?= $totals_venue['quantity_sum'] ?></td>
                    <td class="text-right"><?= number_format($totals_venue['product_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($totals_venue['labour_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($totals_venue['machine_price_sum'], 2) ?></td>
                    <td class="text-right"><?= number_format($totals_venue['total_price_sum'], 2) ?></td>
                </tr>
                <?php
                $totals_items = (new Query())
                    ->select([
                        'item_id',
                        'SUM(quantity) as quantity_sum',
                        'SUM(product_price) as product_price_sum',
                        'SUM(labour_price) as labour_price_sum',
                        'SUM(machine_price) as machine_price_sum',
                        'SUM(total_price) as total_price_sum',
                    ])
                    ->from('product')
                    ->andWhere(['venue_id' => $totals_venue['venue_id']])
                    ->andWhere([
                        'product.deleted_at' => null,
                        'product.status' => $statusList,
                    ])
                    ->groupBy(['item_id'])
                    ->leftJoin('item', 'item_id=item.id')
                    ->orderBy([
                        'item.code' => SORT_ASC,
                    ])
                    ->all(Yii::$app->dbGoldoc);
                foreach ($totals_items as $totals_item) {
                    $item = Item::findOne($totals_item['item_id']);
                    ?>
                    <tr style="background: #eeeeee">
                        <td></td>
                        <td><?= $item ? Html::a($item->code, ['product/index', 'ProductSearch' => [
                                'item_id' => $totals_item['item_id'] ? $totals_item['item_id'] : '-',
                            ]], [
                                //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                                //'data-toggle' => 'tooltip',
                            ]) : '-' ?></td>
                        <td></td>
                        <td class="text-right"><?= $totals_item['quantity_sum'] ?></td>
                        <td class="text-right"><?= number_format($totals_item['product_price_sum'], 2) ?></td>
                        <td class="text-right"><?= number_format($totals_item['labour_price_sum'], 2) ?></td>
                        <td class="text-right"><?= number_format($totals_item['machine_price_sum'], 2) ?></td>
                        <td class="text-right"><?= number_format($totals_item['total_price_sum'], 2) ?></td>
                    </tr>
                    <?php


                    $totals_colours = (new Query())
                        ->select([
                            'colour_id',
                            'SUM(quantity) as quantity_sum',
                            'SUM(product_price) as product_price_sum',
                            'SUM(labour_price) as labour_price_sum',
                            'SUM(machine_price) as machine_price_sum',
                            'SUM(total_price) as total_price_sum',
                        ])
                        ->from('product')
                        ->andWhere(['venue_id' => $totals_venue['venue_id']])
                        ->andWhere(['item_id' => $totals_item['item_id']])
                        ->andWhere([
                            'product.deleted_at' => null,
                            'product.status' => $statusList,
                        ])
                        ->groupBy(['colour_id'])
                        ->leftJoin('colour', 'colour_id=colour.id')
                        ->orderBy([
                            'colour.code' => SORT_ASC,
                        ])
                        ->all(Yii::$app->dbGoldoc);
                    foreach ($totals_colours as $totals_colour) {
                        $colour = Colour::findOne($totals_colour['colour_id']);
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><?= $colour ? Html::a($colour->code, ['product/index', 'ProductSearch' => [
                                    'colour_id' => $totals_colour['colour_id'] ? $totals_colour['colour_id'] : '-',
                                ]], [
                                    //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                                    //'data-toggle' => 'tooltip',
                                ]) : '-' ?></td>
                            <td class="text-right"><?= $totals_colour['quantity_sum'] ?></td>
                            <td class="text-right"><?= number_format($totals_colour['product_price_sum'], 2) ?></td>
                            <td class="text-right"><?= number_format($totals_colour['labour_price_sum'], 2) ?></td>
                            <td class="text-right"><?= number_format($totals_colour['machine_price_sum'], 2) ?></td>
                            <td class="text-right"><?= number_format($totals_colour['total_price_sum'], 2) ?></td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
