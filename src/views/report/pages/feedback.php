<?php

/**
 * @var yii\web\View $this
 */

use app\components\Helper;
use app\components\MenuItem;
use app\models\Feedback;
use app\models\Job;
use app\models\Target;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

//Yii::$app->controller->layout = 'box';
$this->title = Yii::t('app', 'Net Promoter Score');

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
                'body' => Yii::t('app', 'This report is based on Feedback.created_at.'),
                'options' => ['class' => 'alert-info'],
                'closeButton' => false,
            ]);
            // periods
            echo '<h4>Period</h4>';
            echo $this->render('_filter-periods', [
                'period' => $period,
                'url' => ['/report/index', 'report' => 'feedback'],
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
        'url' => ['/report/index', 'report' => 'feedback', 'period' => $period],
    ]);
    ?>


    <?php
    // global
    echo $this->render('_feedback-rep', [
        'staffRep' => false,
        'from' => $from,
        'to' => $to,
    ]);

    if (Yii::$app->user->can('csr')) {
        // per staff
        $staff = [];
        $feedbacks = Feedback::find()
            ->andWhere(['between', 'feedback.created_at', strtotime($from . ' 00:00:00'), strtotime($to . ' 23:59:59')])
            ->andWhere(['IS NOT', 'feedback.score', null])
            ->orderBy(['feedback.score' => SORT_ASC]);

        $staff = [];
        foreach ($feedbacks->all() as $feedback) {
            foreach ($feedback->jobs as $job) {
                if (!isset($staff[$job->staff_rep_id])) {
                    $staff[$job->staff_rep_id] = [
                        'staff_rep_id' => $job->staff_rep_id,
                        'detractors' => 0,
                        'neutrals' => 0,
                        'promoters' => 0,
                        'total' => 0,
                    ];
                }
                $staff[$job->staff_rep_id]['total']++;
                if ($feedback->score <= 6) {
                    $staff[$job->staff_rep_id]['detractors']++;
                } elseif ($feedback->score <= 8) {
                    $staff[$job->staff_rep_id]['neutrals']++;
                } else {
                    $staff[$job->staff_rep_id]['promoters']++;
                }
            }
        }
        foreach ($staff as $k => $a) {
            $staff[$k]['nps'] = round($a['total'] ? ($a['promoters'] - $a['detractors']) / $a['total'] : 0, 2);
        }
        usort($staff, function ($a, $b) {
            $aTotal = $a['total'] ? ($a['promoters'] - $a['detractors']) / $a['total'] : 0;
            $bTotal = $b['total'] ? ($b['promoters'] - $b['detractors']) / $b['total'] : 0;
            return $aTotal < $bTotal;
        });
        foreach ($staff as $counts) {
            echo $this->render('_feedback-rep', [
                'staffRep' => User::findOne($counts['staff_rep_id']),
                'from' => $from,
                'to' => $to,
            ]);
        }
    }
    ?>

</div>