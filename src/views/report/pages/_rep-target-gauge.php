<?php

use app\models\Job;
use app\models\Target;
use app\models\User;
use app\widgets\HighCharts;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * @var View $this
 * @var string $from
 * @var string $to
 * @var User $user
 */

isset($user) || $user = false;
$users = $user ? [$user->id => $user->id] : ArrayHelper::map(User::find()->byRole('rep')->all(), 'id', 'id');
isset($includeQuotes) || $includeQuotes = false;

// find targets
$target = 0;
//$periods = new DatePeriod(new DateTime($from), DateInterval::createFromDateString('1 day'), new DateTime($to . ' +1day'));
//foreach ($periods as $dt) {
/** @var $dt DateTime */
$_targets = Target::find()
    ->where([
        'model_name' => User::className(),
        'model_id' => $users,
        'date' => date('Y-m-d', strtotime($from)),
    ])
    ->all();
foreach ($_targets as $_target) {
    $target += $_target->target;
}
//}

// find sold
$sold = 0;
$jobs = Job::find()
    ->notDeleted()
    ->andWhere([
        'staff_rep_id' => $users,
        'status' => [
            'productionPending' => 'job/productionPending',
            'production' => 'job/production',
            'despatch' => 'job/despatch',
            'packed' => 'job/packed',
            'complete' => 'job/complete',
        ],
    ])
    ->andWhere(['between', 'due_date', date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))])
    ->all();
//$x = [];
foreach ($jobs as $job) {
    if ($job->hideTotals()) {
        continue;
    }
    $sold += $job->getReportTotal();
    if (!isset($x[$job->status])) $x[$job->status] = 0;
    //$x[$job->status] += $job->getReportTotal();
}
//debug($x);

// populate target if empty
if (!$target) {
    $target = $sold;
}

echo HighCharts::widget([
    'modules' => [
        'solid-gauge.src.js',
    ],
    'clientOptions' => [
        'chart' => [
            'type' => 'gauge',
            'height' => 300,
        ],
        'pane' => [
            //'center' => ['50%', '50%'],
            'startAngle' => -125,
            'endAngle' => 125,
            'background' => [
                [
                    'innerRadius' => 0,
                    'outerRadius' => 0,
                    'shape' => 'arc',
                ],
            ],
        ],
        'yAxis' => [
            'plotBands' => [
                [
                    'from' => 0,
                    'to' => $target / 2 * 1.5,  // 50%
                    'outerRadius' => 117,
                    'innerRadius' => 75,
                    'color' => '#FF0000' // red
                ],
                [
                    'from' => $target / 2 * 1.5,  // 50%
                    'to' => $target / 4 * 3 * 1.34,  // 75%
                    'outerRadius' => 117,
                    'innerRadius' => 75,
                    'color' => '#DDDF0D' // yellow
                ],
                [
                    'from' => $target / 4 * 3 * 1.34, // 75%
                    'to' => $target * 1.5,
                    'outerRadius' => 117,
                    'innerRadius' => 75,
                    'color' => '#55BF3B' // green
                ],
            ],

            'lineWidth' => 1,
            'lineColor' => '#222222',

            'minorTickInterval' => 1,
            //'minorTickWidth' => 1,
            //'minorTickLength'=>10,

            //'tickLength' => 10000,
            'tickWidth' => 1,
            'tickColor' => '#222222',

            'labels' => [
                //'enabled' => false,
                'step' => 1,
                'rotation' => 'auto',
                'style' => [
                    'color' => '#222222',
                ],
            ],
            'min' => 0,
            'max' => $target * 1.5,
        ],
        'plotOptions' => [
            'gauge' => [
                'dataLabels' => [
                    'useHTML' => true,
                    'borderWidth' => 0,
                    'y' => 100,
                ],
                'dial' => [
                    'radius' => '75%',
//                    'baseWidth' => 50,
//                    'baseLength' => '200%',
//                    'topWidth' => 1,
                ],
            ],
        ],
        'series' => [[
            'data' => [$sold],
            'dataLabels' => [
                'format' => implode('', [
                    '<div class="text-center" style="font-size:20px">',
                    ($target ? number_format(($sold) / $target * 100, 0) : 0) . '%',
                    '</div>',
                    '<div class="text-center">',
                    '<span class="fa fa-line-chart"></span> ' . number_format(($sold) / 1000, 1) . 'k / ',
                    '<span class="fa fa-bullseye"></span> ' . number_format($target / 1000, 1) . 'k<br>',
                    '</div>',
                ]),
            ],
        ]],
        'title' => [
            'text' => false,
        ],
        'tooltip' => ['enabled' => false],
        'credits' => ['enabled' => false],
        'exporting' => ['enabled' => false],
    ]
]);
