<?php

/**
 * @var yii\web\View $this
 */

use app\components\Helper;
use app\components\MenuItem;
use app\models\Job;
use app\models\Target;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

//Yii::$app->controller->layout = 'box';
$this->title = Yii::t('app', 'Quotes');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

// filter by period/date
$period = Y::GET('period', 'month');
$date = Y::GET('date');
$date = date('Y-m-d', $date ? strtotime($date) : time());
if ($period == 'week') {
    // week
    $from = date('Y-m-d', strtotime('last monday', strtotime($date)));
    $to = date('Y-m-d', strtotime('sunday', strtotime($date)));
    $date = $from;
} elseif ($period == 'month') {
    // month
    $from = date('Y-m-d', strtotime('first day of ' . $date));
    $to = date('Y-m-d', strtotime('last day of ' . $date));
} elseif ($period == 'quarter') {
    // quarter
    $quarter = floor((date('m', strtotime($date)) - 1) / 3) + 1;
    $from = date('Y-m-d', strtotime('first day of ' . date('Y', strtotime($date)) . '-' . ($quarter * 3 - 2) . '-01'));
    $to = date('Y-m-d', strtotime('last day of ' . date('Y', strtotime($date)) . '-' . ($quarter * 3) . '-01'));
    $date = $from;
} else {
    // year
    $from = date('Y-01-01', strtotime($date));
    $to = date('Y-12-31', strtotime($date));
}

?>

<div class="report-rep-performance">

    <div class="box box-default collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Options') ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php
            echo Alert::widget([
                'body' => Yii::t('app', 'This report is based on Job.quote_at.'),
                'options' => ['class' => 'alert-info'],
                'closeButton' => false,
            ]);
            // periods
            echo '<h4>Period</h4>';
            echo $this->render('_filter-periods', [
                'period' => $period,
                'url' => ['/report/index', 'report' => 'quotes'],
            ]);
            ?>
        </div>
    </div>

    <?php
    // paginate periods
    echo $this->render('_paginate_periods', [
        'title' => $this->title,
        'period' => $period,
        'from' => $from,
        'to' => $to,
        'url' => ['/report/index', 'report' => 'quotes', 'period' => $period],
    ]);

    // find jobs
    $jobs = Job::find()
        ->notDeleted()
        ->andWhere(['between', 'quote_at', strtotime($from), strtotime($to)])
        ->all();
    foreach ($jobs as $k => $job) {
        if ($job->status == 'job/draft') unset($jobs[$k]);
        if ($job->status == 'job/quoteLost' && $job->quote_lost_reason = 'phantom') unset($jobs[$k]);
    }
    ?>

    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th width="10%"><?= Yii::t('app', 'Rep Name') ?></th>
            <th width="30%" class="text-center"><?= Yii::t('app', 'Quotes') ?></th>
            <th width="30%" class="text-center"><?= Yii::t('app', 'CSRs') ?></th>
            <th width="30%" class="text-center"><?= Yii::t('app', 'Lead Time') ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?= Yii::t('app', 'All Reps') ?></td>
            <td>
                <?php
                $totals = [];
                $jobPrice = 0;
                foreach ($jobs as $job) {
                    if (!isset($totals[$job->status]))
                        $totals[$job->status] = [
                            'count' => 0,
                            'price' => 0,
                        ];
                    $totals[$job->status]['count']++;
                    $totals[$job->status]['price'] += $job->getReportTotal();
                    $jobPrice += $job->getReportTotal();
                }
                ?>
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th width="60%"><?= Yii::t('app', 'Job Status') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?= Yii::t('app', 'TOTAL') ?></td>
                        <td class="text-right"><?= count($jobs) ?></td>
                        <td class="text-right"><?= number_format($jobPrice, 2) ?></td>
                    </tr>
                    <?php foreach ($totals as $status => $total) { ?>
                        <tr>
                            <td><?= $status ?></td>
                            <td class="text-right"><?= $total['count'] ?></td>
                            <td class="text-right"><?= number_format($total['price'], 2) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
            <td>
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th width="60%"><?= Yii::t('app', 'CSR Name') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $_output = [];
                    // group jobs by csr
                    $csrJobs = [];
                    foreach ($jobs as $_job) {
                        $csrJobs[$_job->staff_csr_id][] = $_job;
                    }
                    foreach ($csrJobs as $staff_csr_id => $__jobs) {
                        /** @var Job[] $__jobs */
                        $staffCsr = User::findOne($staff_csr_id);
                        $_jobPrice = 0;
                        foreach ($__jobs as $_job) {
                            $_jobPrice += $_job->getReportTotal();
                        }
                        ob_start();
                        ?>
                        <tr>
                            <td><?= $staffCsr->label ?></td>
                            <td class="text-right"><?= count($__jobs) ?></td>
                            <td class="text-right">
                                <?= number_format($_jobPrice, 2) ?>
                            </td>
                        </tr>
                        <?php
                        $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                    }
                    krsort($_output);
                    echo implode('', $_output);
                    ?>
                    </tbody>
                </table>
            </td>
            <td>
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th width="60%"><?= Yii::t('app', 'Quoted within 30 Days') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $_output = [];
                    // group jobs by lead time
                    $timeJobs = [];
                    foreach ($jobs as $_job) {
                        $quote_time = Helper::getCompanyQuoteTime($_job->company, $_job);
                        if (in_array($quote_time, ['7 days', '14 days', '30 days'])) {
                            $quote_time = 'new company quotes';
                        } else {
                            $quote_time = 'existing company quotes';
                        }
                        $timeJobs[$quote_time][] = $_job;
                    }
                    foreach ($timeJobs as $quote_time => $__jobs) {
                        /** @var Job[] $__jobs */
                        $_jobPrice = 0;
                        foreach ($__jobs as $_job) {
                            $_jobPrice += $_job->getReportTotal();
                        }
                        ob_start();
                        ?>
                        <tr>
                            <td><?= $quote_time ?></td>
                            <td class="text-right"><?= count($__jobs) ?></td>
                            <td class="text-right">
                                <?= number_format($_jobPrice, 2) ?>
                            </td>
                        </tr>
                        <?php
                        $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                    }
                    krsort($_output);
                    echo implode('', $_output);
                    ?>
                    </tbody>
                </table>
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th width="60%"><?= Yii::t('app', 'Company Create to Job Quote') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                        <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $_output = [];
                    // group jobs by lead time
                    $timeJobs = [];
                    foreach ($jobs as $_job) {
                        $quote_time = Helper::getCompanyQuoteTime($_job->company, $_job);
                        $timeJobs[$quote_time][] = $_job;
                    }
                    foreach ($timeJobs as $quote_time => $__jobs) {
                        /** @var Job[] $__jobs */
                        $_jobPrice = 0;
                        foreach ($__jobs as $_job) {
                            $_jobPrice += $_job->getReportTotal();
                        }
                        ob_start();
                        ?>
                        <tr>
                            <td><?= $quote_time ?></td>
                            <td class="text-right"><?= count($__jobs) ?></td>
                            <td class="text-right">
                                <?= number_format($_jobPrice, 2) ?>
                            </td>
                        </tr>
                        <?php
                        $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                    }
                    krsort($_output);
                    echo implode('', $_output);
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>

        <?php
        $output = [];
        // group jobs by rep
        $repJobs = [];
        foreach ($jobs as $job) {
            $repJobs[$job->staff_rep_id][] = $job;
        }
        foreach ($repJobs as $staff_rep_id => $_jobs) {
            /** @var Job[] $_jobs */
            $staffRep = User::findOne($staff_rep_id);
            $jobPrice = 0;
            foreach ($_jobs as $job) {
                $jobPrice += $job->getReportTotal();
            }
            ob_start();
            ?>
            <tr>
                <td><?= $staffRep->label ?></td>
                <td>
                    <?php
                    $totals = [];
                    foreach ($_jobs as $job) {
                        if (!isset($totals[$job->status]))
                            $totals[$job->status] = [
                                'count' => 0,
                                'price' => 0,
                            ];
                        $totals[$job->status]['count']++;
                        $totals[$job->status]['price'] += $job->getReportTotal();
                    }
                    ?>
                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th width="60%"><?= Yii::t('app', 'Job Status') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?= Yii::t('app', 'TOTAL') ?></td>
                            <td class="text-right"><?= count($_jobs) ?></td>
                            <td class="text-right"><?= number_format($jobPrice, 2) ?></td>
                        </tr>
                        <?php foreach ($totals as $status => $total) { ?>
                            <tr>
                                <td><?= $status ?></td>
                                <td class="text-right"><?= $total['count'] ?></td>
                                <td class="text-right"><?= number_format($total['price'], 2) ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th width="60%"><?= Yii::t('app', 'CSR Name') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $_output = [];
                        // group jobs by csr
                        $csrJobs = [];
                        foreach ($_jobs as $_job) {
                            $csrJobs[$_job->staff_csr_id][] = $_job;
                        }
                        foreach ($csrJobs as $staff_csr_id => $__jobs) {
                            /** @var Job[] $__jobs */
                            $staffCsr = User::findOne($staff_csr_id);
                            $_jobPrice = 0;
                            foreach ($__jobs as $_job) {
                                $_jobPrice += $_job->getReportTotal();
                            }
                            ob_start();
                            ?>
                            <tr>
                                <td><?= $staffCsr->label ?></td>
                                <td class="text-right"><?= count($__jobs) ?></td>
                                <td class="text-right">
                                    <?= number_format($_jobPrice, 2) ?>
                                </td>
                            </tr>
                            <?php
                            $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                        }
                        krsort($_output);
                        echo implode('', $_output);
                        ?>
                        </tbody>
                    </table>

                </td>
                <td>
                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th width="60%"><?= Yii::t('app', 'Quoted within 30 Days') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $_output = [];
                        // group jobs by lead time
                        $timeJobs = [];
                        foreach ($_jobs as $_job) {
                            $quote_time = Helper::getCompanyQuoteTime($_job->company, $_job);
                            $timeJobs[$quote_time][] = $_job;
                        }
                        foreach ($timeJobs as $quote_time => $__jobs) {
                            /** @var Job[] $__jobs */
                            $_jobPrice = 0;
                            foreach ($__jobs as $_job) {
                                $_jobPrice += $_job->getReportTotal();
                            }
                            ob_start();
                            ?>
                            <tr>
                                <td><?= $quote_time ?></td>
                                <td class="text-right"><?= count($__jobs) ?></td>
                                <td class="text-right">
                                    <?= number_format($_jobPrice, 2) ?>
                                </td>
                            </tr>
                            <?php
                            $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                        }
                        krsort($_output);
                        echo implode('', $_output);
                        ?>
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered">
                        <thead>
                        <tr>
                            <th width="60%"><?= Yii::t('app', 'Company Create to Job Quote') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Count') ?></th>
                            <th width="20%" class="text-center"><?= Yii::t('app', 'Price') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $_output = [];
                        // group jobs by lead time
                        $timeJobs = [];
                        foreach ($_jobs as $_job) {
                            $quote_time = Helper::getCompanyQuoteTime($_job->company, $_job);
                            if (in_array($quote_time, ['7 days', '14 days', '30 days'])) {
                                $quote_time = 'new company quotes';
                            } else {
                                $quote_time = 'existing company quotes';
                            }
                            $timeJobs[$quote_time][] = $_job;
                        }
                        foreach ($timeJobs as $quote_time => $__jobs) {
                            /** @var Job[] $__jobs */
                            $_jobPrice = 0;
                            foreach ($__jobs as $_job) {
                                $_jobPrice += $_job->getReportTotal();
                            }
                            ob_start();
                            ?>
                            <tr>
                                <td><?= $quote_time ?></td>
                                <td class="text-right"><?= count($__jobs) ?></td>
                                <td class="text-right">
                                    <?= number_format($_jobPrice, 2) ?>
                                </td>
                            </tr>
                            <?php
                            $_output[sprintf('%20d', $_jobPrice) . '.' . uniqid()] = ob_get_clean();
                        }
                        krsort($_output);
                        echo implode('', $_output);
                        ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php
            $output[sprintf('%20d', $jobPrice) . '.' . uniqid()] = ob_get_clean();
        }
        krsort($output);
        echo implode('', $output);
        ?>
        </tbody>
    </table>

</div>