<?php

/**
 * @var yii\web\View $this
 */

use app\components\Helper;
use app\components\MenuItem;
use app\models\Company;
use app\models\User;
use app\widgets\HighCharts;
use cornernote\shortcuts\Y;

$this->title = Yii::t('app', 'New Companies');

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

<div class="report-new-companies">

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
            // periods
            echo '<h4>Period</h4>';
            echo $this->render('_filter-periods', [
                'period' => $period,
                'url' => ['/report/index', 'report' => 'new-companies'],
            ]);
            ?>
        </div>
    </div>

    <?php
    // get report data
    $companies = Company::find()
        ->notDeleted()
        ->andWhere(['between', 'created_at', strtotime($from . ' 00:00:00'), strtotime($to . ' 23:59:59')])
        ->all();
    $totals = [];
    $quoteTimes = ['7 days', '14 days', '30 days', '60 days', '90 days', 'none'];
    $jobTypes = [];
    $industries = [];
    foreach ($companies as $company) {
        $user_id = $company->staff_rep_id ?: 0;
        $jobTypes[$company->job_type_id] = $company->jobType ? $company->jobType->name : '';
        $industries[$company->industry_id] = $company->industry ? $company->industry->name : '';
        $quote_time = Helper::getCompanyQuoteTime($company);
        if (!isset($totals[$user_id]))
            $totals[$user_id] = [
                'user_id' => $user_id,
                'total' => 0,
                'job_type' => [],
                'industry' => [],
            ];
        if (!isset($totals[$user_id]['quote_time'][$quote_time]))
            $totals[$user_id]['quote_time'][$quote_time] = 0;
        if (!isset($totals[$user_id]['job_type'][$company->job_type_id ?: 0]))
            $totals[$user_id]['job_type'][$company->job_type_id ?: 0] = 0;
        if (!isset($totals[$user_id]['industry'][$company->industry_id ?: 0]))
            $totals[$user_id]['industry'][$company->industry_id ?: 0] = 0;

        $totals[$user_id]['total']++;
        $totals[$user_id]['quote_time'][$quote_time]++;
        $totals[$user_id]['job_type'][$company->job_type_id ?: 0]++;
        $totals[$user_id]['industry'][$company->industry_id ?: 0]++;
    }

    // sort the totals
    $_s = [];
    foreach ($totals as $k => $v)
        $_s[$k] = $v['total'];
    array_multisort($_s, SORT_DESC, $totals);

    // format data for series
    $data = [
        'total' => [],
        'quote_time' => [],
        'job_type' => [],
        'industry' => [],
    ];
    $categories = [];
    foreach ($totals as $counts) {
        $user = User::findOne($counts['user_id']);
        $categories[] = $user ? $user->label : 'Unassigned';
        $data['total'][] = $counts['total'];
        foreach ($quoteTimes as $k) {
            $data['quote_time'][$k][] = isset($counts['quote_time'][$k]) ? $counts['quote_time'][$k] : 0;
        }
        foreach ($jobTypes as $k => $v) {
            $data['job_type'][$k][] = isset($counts['job_type'][$k]) ? $counts['job_type'][$k] : 0;
        }
        foreach ($industries as $k => $v) {
            $data['industry'][$k][] = isset($counts['industry'][$k]) ? $counts['industry'][$k] : 0;
        }
    }

    // format series for chart
    $series = [];
    $series[] = [
        'name' => Yii::t('app', 'Total'),
        'data' => array_values($data['total']),
    ];
    foreach ($quoteTimes as $k) {
        $series[] = [
            'name' => $k ? 'QT:' . $k : 'QT: Not Set',
            'data' => $data['quote_time'] ? array_values($data['quote_time'][$k]) : [],
            'stack' => 'quote_time',
            'visible' => true,
        ];
    }
    foreach ($jobTypes as $k => $v) {
        $series[] = [
            'name' => $v ? 'JT:' . $v : 'JT: Not Set',
            'data' => $data['job_type'] ? array_values($data['job_type'][$k]) : [],
            'stack' => 'job_type',
            'visible' => false,
        ];
    }
    foreach ($industries as $k => $v) {
        $series[] = [
            'name' => $v ? 'Ind:' . $v : 'Ind: Not Set',
            'data' => $data['industry'] ? array_values($data['industry'][$k]) : [],
            'stack' => 'industry',
            'visible' => false,
        ];
    }

    // paginate periods
    echo $this->render('_paginate_periods', [
        'title' => count($companies) . ' ' . $this->title,
        'period' => $period,
        'from' => $from,
        'to' => $to,
        'url' => ['/report/index', 'report' => 'new-companies', 'period' => $period],
    ]);

    // render chart
    echo HighCharts::widget([
        'clientOptions' => [
            'chart' => ['type' => 'column', 'height' => 450],
            'plotOptions' => [
                'column' => [
                    'stacking' => 'normal',
                    'dataLabels' => [
                        'enabled' => true,
                    ],
                ],
            ],
            'xAxis' => ['categories' => array_values($categories)],
            'yAxis' => [
                'title' => ['text' => false],
            ],
            'series' => $series,
            'tooltip' => [
                'shared' => true,
                'crosshairs' => true,
            ],
            'title' => ['text' => false],
            'credits' => ['enabled' => false],
            'exporting' => ['enabled' => false],
        ]
    ]);

    ?>

</div>