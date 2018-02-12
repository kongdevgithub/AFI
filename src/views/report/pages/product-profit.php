<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Product;
use app\models\ProductType;
use app\models\Job;
use cornernote\shortcuts\Y;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'Product Profit');

Yii::$app->controller->layout = 'narrow';
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

// set date
$date = Y::GET('date');
$date = date('Y-m-d', $date ? strtotime($date) : time());
$from = date('Y-m-d 00:00:00', strtotime('first day of ' . $date));
$to = date('Y-m-d 23:59:59', strtotime('last day of ' . $date));
$paginateDateFormat = 'F Y';
$nextFormat = '1 month';
$includeQuotes = false; //$from == date('Y-m-d 00:00:00', strtotime('first day of'));

// set product types
$productTypes = ProductType::getDropdownOpts();
?>

<div class="report-product-profit">

    <div class="box box-default">
        <div class="box-body">

            <div class="row">
                <div class="col-sm-3 col-md-3">
                    <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'product-profit', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
                <div class="col-sm-6 col-md-6 text-center">
                    <h2 style="margin-top: 0"><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
                    <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
                </div>
                <div class="col-sm-3 col-md-3 text-right">
                    <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'product-profit', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
            </div>


            <table class="table table-condensed table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Name') ?></th>
                    <th class="text-center"><?= Yii::t('app', 'Stock Cost') ?></th>
                    <th class="text-center"><?= Yii::t('app', 'Sales') ?></th>
                    <th class="text-center"><?= Yii::t('app', 'Count') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // global
                // find products
                $products = Product::find()
                    ->notDeleted()
                    ->joinWith(['job'])
                    ->andWhere('job.deleted_at IS NULL')
                    ->andWhere('product.quote_quantity > 0')
                    ->andWhere('product.fork_quantity_product_id IS NULL')
                    ->andWhere('job.status=:quote OR ((job.status=:productionPending OR job.status=:production OR job.status=:despatch OR job.status=:packed OR job.status=:complete) AND job.due_date BETWEEN :from AND :to)', [
                        'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                        'productionPending' => 'job/productionPending',
                        'production' => 'job/production',
                        'despatch' => 'job/despatch',
                        'packed' => 'job/packed',
                        'complete' => 'job/complete',
                        'from' => date('Y-m-d', strtotime($from)),
                        'to' => date('Y-m-d', strtotime($to)),
                    ]);
                $cost = 0;
                $sell = 0;
                $count = 0;
                foreach ($products->each(100) as $product) {
                    /** @var Product $product */
                    if ($product->job->hideTotals()) {
                        continue;
                    }
                    $winFactor = $product->job->status == 'job/quote' ? ($product->job->quote_win_chance / 100) : 1;
                    $productPercent = $product->job->quote_wholesale_price != 0 ? ($product->quote_factor_price - $product->quote_discount_price) / $product->job->quote_wholesale_price : 0;
                    $cost += $product->getStockCost() * $winFactor * $productPercent;
                    $sell += $product->job->getReportTotal() * $winFactor * $productPercent;
                    $count++;
                }
                ?>
                <tr>
                    <td><?= Yii::t('app', 'All Products') ?></td>
                    <td class="text-right"><?= number_format($cost, 2) ?></td>
                    <td class="text-right"><?= number_format($sell, 2) ?></td>
                    <td class="text-right"><?= number_format($count, 0) ?></td>
                </tr>


                <?php
                // for each product type
                foreach (array_keys($productTypes) as $product_type_id) {
                    $productType = ProductType::findOne($product_type_id);
                    $productTypeIds = ArrayHelper::merge([$productType->id => $productType->id], ArrayHelper::map($productType->getDropdownOptsChildren([]), 'id', 'id'));

                    // find products
                    $products = Product::find()
                        ->notDeleted()
                        ->joinWith(['job'])
                        ->andWhere('job.deleted_at IS NULL')
                        ->andWhere('product.quote_quantity > 0')
                        ->andWhere('product.fork_quantity_product_id IS NULL')
                        ->andWhere(['product_type_id' => $productTypeIds])
                        ->andWhere('job.status=:quote OR ((job.status=:productionPending OR job.status=:production OR job.status=:despatch OR job.status=:packed OR job.status=:complete) AND job.due_date BETWEEN :from AND :to)', [
                            'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                            'productionPending' => 'job/productionPending',
                            'production' => 'job/production',
                            'despatch' => 'job/despatch',
                            'packed' => 'job/packed',
                            'complete' => 'job/complete',
                            'from' => date('Y-m-d', strtotime($from)),
                            'to' => date('Y-m-d', strtotime($to)),
                        ]);

                    $cost = 0;
                    $sell = 0;
                    $count = 0;
                    foreach ($products->all() as $product) {
                        if ($product->job->hideTotals()) {
                            continue;
                        }
                        $winFactor = $product->job->status == 'job/quote' ? ($product->job->quote_win_chance / 100) : 1;
                        $productPercent = $product->job->quote_wholesale_price != 0 ? ($product->quote_factor_price - $product->quote_discount_price) / $product->job->quote_wholesale_price : 0;
                        $cost += $product->getStockCost() * $winFactor * $productPercent;
                        $sell += $product->job->getReportTotal() * $winFactor * $productPercent;
                        $count++;
                    }

                    ?>
                    <tr>
                        <td><?= Html::a($productType->getBreadcrumbString(), ['product-profit-detail', 'date' => $date, 'product_type_id' => $productType->id]) ?></td>
                        <td class="text-right"><?= number_format($cost, 2) ?></td>
                        <td class="text-right"><?= number_format($sell, 2) ?></td>
                        <td class="text-right"><?= number_format($count, 0) ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

        </div>
    </div>

</div>