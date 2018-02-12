<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Product;
use app\models\ProductType;
use app\models\Job;
use cornernote\shortcuts\Y;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'Product ReDos');

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
$productTypes = ProductType::find()
    ->notDeleted()
    ->orderBy(['name' => SORT_ASC])
    ->all();
?>

<div class="report-product-redos">

    <div class="box box-default">
        <div class="box-body">

            <div class="row">
                <div class="col-sm-3 col-md-3">
                    <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'product-redos', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
                <div class="col-sm-6 col-md-6 text-center">
                    <h2 style="margin-top: 0"><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
                    <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
                </div>
                <div class="col-sm-3 col-md-3 text-right">
                    <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'product-redos', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
            </div>


            <table class="table table-condensed table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Name') ?></th>
                    <th><?= Yii::t('app', 'ReDos') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // for each product type
                $output = [];
                foreach ($productTypes as $_productType) {
                    // find products
                    $products = Product::find()
                        ->notDeleted()
                        ->joinWith(['job'])
                        ->andWhere('job.deleted_at IS NULL')
                        ->andWhere('product.quote_quantity > 0')
                        ->andWhere('product.fork_quantity_product_id IS NULL')
                        ->andWhere(['product_type_id' => $_productType->id])
                        ->andWhere('job.status=:quote OR ((job.status=:production OR job.status=:complete) AND job.due_date BETWEEN :from AND :to)', [
                            'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                            'production' => 'job/production',
                            'complete' => 'job/complete',
                            'from' => date('Y-m-d', strtotime($from)),
                            'to' => date('Y-m-d', strtotime($to)),
                        ])
                        ->andWhere(['not', ['redo_job_id' => null]]);
                    if (!Yii::$app->user->can('manager')) {
                        $products->andWhere(['or',
                            ['job.staff_rep_id' => Yii::$app->user->id],
                            ['job.staff_csr_id' => Yii::$app->user->id],
                        ]);
                    }
                    if (!$products->count()) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td><?= $_productType->getBreadcrumbString() ?></td>
                        <td>
                            <?php
                            $items = [];
                            foreach ($products->all() as $product) {
                                $items[$product->job->id] = $product->job->getLink($product->job->getTitle());
                            }
                            echo Html::ul($items, ['encode' => false]);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

        </div>
    </div>

</div>