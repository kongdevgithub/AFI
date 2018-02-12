<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Company;
use app\models\Job;
use cornernote\shortcuts\Y;
use yii\helpers\Html;
use yii\widgets\DetailView;

Yii::$app->controller->layout = 'box';
$this->title = Yii::t('app', 'Company Performance');

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

// set companies
$companies = Company::find()
    ->notDeleted()
    ->orderBy(['name' => SORT_ASC])
    ->all();
?>

<div class="report-company-performance">

    <div class="row">
        <div class="col-sm-3 col-md-3">
            <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'company-performance', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
        <div class="col-sm-6 col-md-6 text-center">
            <h2 style="margin-top: 0;"><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
            <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
        </div>
        <div class="col-sm-3 col-md-3 text-right">
            <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'company-performance', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
    </div>

    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th></th>
            <th class="text-center" colspan="5"><?= Yii::t('app', 'Discounts') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Product Factors') ?></th>
            <th class="text-center" colspan="4"><?= Yii::t('app', 'Job Margins') ?></th>
        </tr>
        <tr>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Job') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Product') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Total') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Maximum') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Offset') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Offset') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Cost') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Sell') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Margin') ?></th>
            <th class="text-center"><?= Yii::t('app', 'Percent') ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        // global
        // find jobs
        $jobs = Job::find()
            ->notDeleted()
            //->andWhere(['company_id' => ArrayHelper::map($companies, 'id', 'id')])
            ->andWhere('status=:quote OR ((status=:productionPending OR status=:production OR status=:despatch OR status=:packed OR status=:complete) AND due_date BETWEEN :from AND :to)', [
                'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                'productionPending' => 'job/productionPending',
                'production' => 'job/production',
                'despatch' => 'job/despatch',
                'packed' => 'job/packed',
                'complete' => 'job/complete',
                'from' => date('Y-m-d', strtotime($from)),
                'to' => date('Y-m-d', strtotime($to)),
            ])
            ->all();
        $jobDiscount = 0;
        $productDiscount = 0;
        $maximumDiscount = 0;
        $cost = 0;
        $sell = 0;
        $productFactorOffset = 0;
        foreach ($jobs as $job) {
            if ($job->hideTotals()) {
                continue;
            }
            $winFactor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
            $jobDiscount += $job->quote_discount_price * $winFactor;
            $productDiscount += $job->getProductDiscount() * $winFactor;
            $maximumDiscount += $job->quote_maximum_discount_price * $job->quote_markup * $winFactor;
            $cost += $job->quote_total_cost * $winFactor;
            $sell += $job->getReportTotal() * $winFactor;
            //$productFactorOffset += (1 - $product->quote_factor) * $product->quote_total_price * $job->quote_markup * -1 * $winFactor;
        }
        ?>
        <tr>
            <td><?= Yii::t('app', 'All Companies') ?></td>
            <td class="text-right"><?= number_format($jobDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($productDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($jobDiscount + $productDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($maximumDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($maximumDiscount - $jobDiscount - $productDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($productFactorOffset, 2) ?></td>
            <td class="text-right"><?= number_format($cost, 2) ?></td>
            <td class="text-right"><?= number_format($sell, 2) ?></td>
            <td class="text-right"><?= number_format($sell - $cost, 2) ?></td>
            <td class="text-right"><?= round(($sell ? ($sell - $cost) / $sell : 0) * 100) ?></td>
        </tr>


        <?php
        // for each company
        $output = [];
        foreach ($companies as $_company) {
            // find products
            // find jobs
            $jobs = Job::find()
                ->notDeleted()
                ->andWhere(['company_id' => $_company->id])
                ->andWhere('status=:quote OR ((status=:production OR status=:complete) AND due_date BETWEEN :from AND :to)', [
                    'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                    'production' => 'job/production',
                    'complete' => 'job/complete',
                    'from' => date('Y-m-d', strtotime($from)),
                    'to' => date('Y-m-d', strtotime($to)),
                ])
                ->all();
            if (!$jobs) {
                continue;
            }
            $jobDiscount = 0;
            $productDiscount = 0;
            $maximumDiscount = 0;
            $cost = 0;
            $sell = 0;
            $productFactorOffset = 0;
            foreach ($jobs as $job) {
                if ($job->hideTotals()) {
                    continue;
                }
                $winFactor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
                $jobDiscount += $job->quote_discount_price * $winFactor;
                $productDiscount += $job->getProductDiscount() * $winFactor;
                $maximumDiscount += $job->quote_maximum_discount_price * $job->quote_markup * $winFactor;
                $cost += $job->quote_total_cost * $winFactor;
                $sell += $job->getReportTotal() * $winFactor;
                //$productFactorOffset += (1 - $product->quote_factor) * $product->quote_total_price * $job->quote_markup * -1 * $winFactor;
            }
            ob_start();
            ?>
            <tr>
                <td><?= $_company->name ?></td>
                <td class="text-right"><?= number_format($jobDiscount, 2) ?></td>
                <td class="text-right"><?= number_format($productDiscount, 2) ?></td>
                <td class="text-right"><?= number_format($jobDiscount + $productDiscount, 2) ?></td>
                <td class="text-right"><?= number_format($maximumDiscount, 2) ?></td>
                <td class="text-right"><?= number_format($maximumDiscount - $jobDiscount - $productDiscount, 2) ?></td>
                <td class="text-right"><?= number_format($productFactorOffset, 2) ?></td>
                <td class="text-right"><?= number_format($cost, 2) ?></td>
                <td class="text-right"><?= number_format($sell, 2) ?></td>
                <td class="text-right"><?= number_format($sell - $cost, 2) ?></td>
                <td class="text-right"><?= round(($sell ? ($sell - $cost) / $sell : 0) * 100) ?></td>
            </tr>
            <?php
            $sell = 0;
            foreach ($jobs as $job) {
                $factor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
                $sell += $job->getReportTotal() * $factor;
            }
            if ($sell) {
                $output[sprintf('%20d', $sell) . '.' . uniqid()] = ob_get_clean();
            } else {
                ob_end_clean();
            }
        }
        krsort($output);
        echo implode('', $output);
        ?>
        </tbody>
    </table>

</div>