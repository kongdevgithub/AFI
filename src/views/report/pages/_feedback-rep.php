<?php
/**
 * @var View $this
 * @var User $staffRep
 * @var User $staffRep
 * @var string $period
 * @var string $from
 * @var string $to
 */

use app\models\Feedback;
use app\models\User;
use yii\web\View;

$staffRep = isset($staffRep) ? $staffRep : false;

// find feedbacks
$feedbacks = Feedback::find()
    ->andWhere(['between', 'feedback.created_at', strtotime($from . ' 00:00:00'), strtotime($to . ' 23:59:59')])
    ->andWhere(['IS NOT', 'feedback.score', null])
    ->orderBy(['feedback.score' => SORT_ASC]);
if ($staffRep) {
    $feedbacks->joinWith(['jobs']);
    $feedbacks->andWhere(['job.staff_rep_id' => $staffRep->id]);
}

$contactCount = [
    'detractors' => 0,
    'neutrals' => 0,
    'promoters' => 0,
    'total' => 0,
];
$jobCount = [
    'detractors' => 0,
    'neutrals' => 0,
    'promoters' => 0,
    'total' => 0,
];
$jobValue = [
    'detractors' => 0,
    'neutrals' => 0,
    'promoters' => 0,
    'total' => 0,
];

foreach ($feedbacks->all() as $feedback) {
    $_jobCount = count($feedback->jobs);
    $_jobValue = 0;
    foreach ($feedback->jobs as $job) {
        $_jobValue += $job->getReportTotal();
    }
    $contactCount['total']++;
    $jobCount['total'] += $_jobCount;
    $jobValue['total'] += $_jobValue;
    if ($feedback->score <= 6) {
        $contactCount['detractors']++;
        $jobCount['detractors'] += $_jobCount;
        $jobValue['detractors'] += $_jobValue;
    } elseif ($feedback->score <= 8) {
        $contactCount['neutrals']++;
        $jobCount['neutrals'] += $_jobCount;
        $jobValue['neutrals'] += $_jobValue;
    } else {
        $contactCount['promoters']++;
        $jobCount['promoters'] += $_jobCount;
        $jobValue['promoters'] += $_jobValue;
    }
}
?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $staffRep ? $staffRep->label : Yii::t('app', 'Company Wide') ?></h3>
    </div>
    <div class="box-body">

        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_feedback-panel', [
                    'label' => Yii::t('app', 'Net Promoter Score'),
                    'icon' => 'fa fa-bar-chart',
                    'color' => 'blue',
                    'contactCount' => $contactCount['promoters'] - $contactCount['detractors'],
                    'contactCountPercent' => $contactCount['total'] ? ($contactCount['promoters'] - $contactCount['detractors']) / $contactCount['total'] * 100 : 0,
                    'jobCount' => $jobCount['promoters'] - $jobCount['detractors'],
                    'jobCountPercent' => $jobCount['total'] ? ($jobCount['promoters'] - $jobCount['detractors']) / $jobCount['total'] * 100 : 0,
                    'jobValue' => $jobValue['promoters'] - $jobValue['detractors'],
                    'jobValuePercent' => $jobValue['total'] ? ($jobValue['promoters'] - $jobValue['detractors']) / $jobValue['total'] * 100 : 0,
                    'url' => ['/report/index', 'report' => 'feedback-details', 'from' => $from, 'to' => $to, 'staff_rep_id' => $staffRep ? $staffRep->id : null],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $this->render('_feedback-panel', [
                    'label' => Yii::t('app', 'Promoters'),
                    'icon' => 'fa fa-smile-o',
                    'color' => 'green',
                    'contactCount' => $contactCount['promoters'],
                    'contactCountPercent' => $contactCount['total'] ? $contactCount['promoters'] / $contactCount['total'] * 100 : 0,
                    'jobCount' => $jobCount['promoters'],
                    'jobCountPercent' => $jobCount['total'] ? $jobCount['promoters'] / $jobCount['total'] * 100 : 0,
                    'jobValue' => $jobValue['promoters'],
                    'jobValuePercent' => $jobValue['total'] ? $jobValue['promoters'] / $jobValue['total'] * 100 : 0,
                    'url' => ['/report/index', 'report' => 'feedback-details', 'from' => $from, 'to' => $to, 'staff_rep_id' => $staffRep ? $staffRep->id : null, 'type' => 'promoters'],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $this->render('_feedback-panel', [
                    'label' => Yii::t('app', 'Neutrals'),
                    'icon' => 'fa fa-meh-o',
                    'color' => 'yellow',
                    'contactCount' => $contactCount['neutrals'],
                    'contactCountPercent' => $contactCount['total'] ? $contactCount['neutrals'] / $contactCount['total'] * 100 : 0,
                    'jobCount' => $jobCount['neutrals'],
                    'jobCountPercent' => $jobCount['total'] ? $jobCount['neutrals'] / $jobCount['total'] * 100 : 0,
                    'jobValue' => $jobValue['neutrals'],
                    'jobValuePercent' => $jobValue['total'] ? $jobValue['neutrals'] / $jobValue['total'] * 100 : 0,
                    'url' => ['/report/index', 'report' => 'feedback-details', 'from' => $from, 'to' => $to, 'staff_rep_id' => $staffRep ? $staffRep->id : null, 'type' => 'neutrals'],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $this->render('_feedback-panel', [
                    'label' => Yii::t('app', 'Detractors'),
                    'icon' => 'fa fa-frown-o',
                    'color' => 'red',
                    'contactCount' => $contactCount['detractors'],
                    'contactCountPercent' => $contactCount['total'] ? $contactCount['detractors'] / $contactCount['total'] * 100 : 0,
                    'jobCount' => $jobCount['detractors'],
                    'jobCountPercent' => $jobCount['total'] ? $jobCount['detractors'] / $jobCount['total'] * 100 : 0,
                    'jobValue' => $jobValue['detractors'],
                    'jobValuePercent' => $jobValue['total'] ? $jobValue['detractors'] / $jobValue['total'] * 100 : 0,
                    'url' => ['/report/index', 'report' => 'feedback-details', 'from' => $from, 'to' => $to, 'staff_rep_id' => $staffRep ? $staffRep->id : null, 'type' => 'detractors'],
                ]) ?>
            </div>
        </div>

    </div>
</div>
