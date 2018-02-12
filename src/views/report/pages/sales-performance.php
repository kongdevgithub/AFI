<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Job;
use app\models\Target;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Sales Performance');

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

// set users
$users = User::find()
    //->byRole('rep')
    ->orderBy(['username' => SORT_ASC])
    ->all();
?>

<div class="report-sales-performance">

    <div class="row">
        <div class="col-sm-3 col-md-3">
            <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'sales-performance', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
        <div class="col-sm-6 col-md-6 text-center">
            <h2><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
            <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
        </div>
        <div class="col-sm-3 col-md-3 text-right">
            <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'sales-performance', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
    </div>


    <?php
    // global
    // find jobs
    $jobs = Job::find()
        ->notDeleted()
        //->andWhere(['staff_rep_id' => ArrayHelper::map($users, 'id', 'id')])
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
    ?>
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <h3 class="panel-title pull-left">
                <?= Yii::t('app', 'All Reps') ?>
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $this->render('_rep-target-gauge', [
                        'from' => $from,
                        'to' => $to,
                    ]) ?>
                </div>
                <?php if (Yii::$app->user->can('manager')) { ?>
                    <div class="col-md-3">
                        <?= $this->render('_job-discounts', [
                            'jobs' => $jobs,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->render('_job-product-margins', [
                            'jobs' => $jobs,
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->render('_job-margins', [
                            'jobs' => $jobs,
                        ]) ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>


    <?php
    // for each user
    $output = [];
    foreach ($users as $_user) {
        ob_start();
        // find jobs
        $jobs = Job::find()
            ->notDeleted()
            ->andWhere(['staff_rep_id' => $_user->id])
            ->andWhere(['status' => [
                'productionPending' => 'job/productionPending',
                'production' => 'job/production',
                'despatch' => 'job/despatch',
                'packed' => 'job/packed',
                'complete' => 'job/complete',
            ]])
            ->andWhere(['between', 'due_date', date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))])
            ->all();
        if (!$jobs) {
            ob_end_clean();
            continue;
        }
        ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title pull-left">
                    <?= $_user->label ?>
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $this->render('_rep-target-gauge', [
                            'user' => $_user,
                            'from' => $from,
                            'to' => $to,
                        ]) ?>
                    </div>
                    <?php if (Yii::$app->user->can('manager')) { ?>
                        <div class="col-md-3">
                            <?= $this->render('_job-discounts', ['jobs' => $jobs]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->render('_job-product-margins', ['jobs' => $jobs]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->render('_job-margins', ['jobs' => $jobs]) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        $target = 0;
        $_target = Target::find()
            ->where([
                'model_name' => $_user->className(),
                'model_id' => $_user->id,
                'date' => date('Y-m-d', strtotime($from)),
            ])
            ->one();
        if ($_target) {
            $target += $_target->target;
        }
        $sell = 0;
        foreach ($jobs as $job) {
            $factor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
            $sell += $job->getReportTotal() * $factor;
        }
        $output[($target ? $sell / $target : 0) . '.' . uniqid()] = ob_get_clean();
    }
    krsort($output);
    echo implode('', $output);
    ?>
</div>