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

Yii::$app->controller->layout = 'narrow';
$this->title = Yii::t('app', 'Company Sales');

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

<div class="report-company-sales">


    <div class="box box-default">
        <div class="box-body">

            <div class="row">
                <div class="col-sm-3 col-md-3">
                    <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'company-sales', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
                <div class="col-sm-6 col-md-6 text-center">
                    <h2 style="margin-top: 0;"><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
                    <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
                </div>
                <div class="col-sm-3 col-md-3 text-right">
                    <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'company-sales', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
                </div>
            </div>

            <table class="table table-condensed table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Name') ?></th>
                    <th class="text-center"><?= Yii::t('app', 'Sales') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // global
                // find jobs
                $jobQuery = Job::find()
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
                    ]);
                if (!Yii::$app->user->can('manager')) {
                    $jobQuery->andWhere(['or',
                        ['staff_rep_id' => Yii::$app->user->id],
                        ['staff_csr_id' => Yii::$app->user->id],
                    ]);
                }
                $jobs = $jobQuery->all();
                $sell = 0;
                foreach ($jobs as $job) {
                    if ($job->hideTotals()) {
                        continue;
                    }
                    $winFactor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
                    $sell += $job->getReportTotal() * $winFactor;
                }
                ?>
                <tr>
                    <td><?= Yii::t('app', 'All Companies') ?></td>
                    <td class="text-right"><?= number_format($sell, 2) ?></td>
                </tr>


                <?php
                // for each company
                $output = [];
                foreach ($companies as $_company) {
                    // find products
                    // find jobs
                    $jobQuery = Job::find()
                        ->notDeleted()
                        ->andWhere(['company_id' => $_company->id])
                        ->andWhere('status=:quote OR ((status=:production OR status=:complete) AND due_date BETWEEN :from AND :to)', [
                            'quote' => $includeQuotes ? 'job/quote' : 'job/fake',
                            'production' => 'job/production',
                            'complete' => 'job/complete',
                            'from' => date('Y-m-d', strtotime($from)),
                            'to' => date('Y-m-d', strtotime($to)),
                        ]);
                    if (!Yii::$app->user->can('manager')) {
                        $jobQuery->andWhere(['or',
                            ['staff_rep_id' => Yii::$app->user->id],
                            ['staff_csr_id' => Yii::$app->user->id],
                        ]);
                    }
                    $jobs = $jobQuery->all();
                    if (!$jobs) {
                        continue;
                    }
                    $sell = 0;
                    foreach ($jobs as $job) {
                        if ($job->hideTotals()) {
                            continue;
                        }
                        $winFactor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
                        $sell += $job->getReportTotal() * $winFactor;
                        //$productFactorOffset += (1 - $product->quote_factor) * $product->quote_total_price * $job->quote_markup * -1 * $winFactor;
                    }
                    ob_start();
                    ?>
                    <tr>
                        <td><?= $_company->name ?></td>
                        <td class="text-right"><?= number_format($sell, 2) ?></td>
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
    </div>


</div>