<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Job;
use app\models\search\JobSearch;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'Sales Pipeline');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();


$searchParams = [];

$staffReps = User::find()->byRole('rep')->all();
$staff_rep_id = Y::GET('staff_rep_id') ?: Yii::$app->user->id;
if (!$staff_rep_id || !in_array($staff_rep_id, ArrayHelper::map($staffReps, 'id', 'id'))) {
    $staff_rep_id = 'all';
}
$staffRep = $staff_rep_id != 'all' ? User::findOne($staff_rep_id) : false;
if ($staff_rep_id != 'all') {
    $searchParams['JobSearch']['staff_rep_id'] = $staff_rep_id;
}


$showColumns = ['name', 'name.name', 'name.name.company', 'name.name.staff', 'name.links', 'name.dates', 'report_total', 'status'];
?>

<div class="report-sales-pipeline">

    <?php if (Yii::$app->user->can('manager')) { ?>
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Filters') ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php
                // staff_rep
                if (Yii::$app->user->can('manager')) {
                    echo '<h4>Staff Rep</h4>';
                    echo $this->render('_filter-staff', [
                        'staff' => $staffRep,
                        'staffUrlParam' => 'staff_rep_id',
                        'role' => 'rep',
                        'url' => ['/report/index', 'report' => 'sales-pipeline'],
                    ]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-sm-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Monthly Target') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <?= $this->render('/report/pages/_rep-target-gauge', [
                        'user' => $staffRep,
                        'from' => date('Y-m-d 00:00:00', strtotime('first day of')),
                        'to' => date('Y-m-d 23:59:59', strtotime('last day of')),
                    ]) ?>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Production Totals by Job Status'); ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    $total = 0;
                    $attributes = [];
                    foreach (['productionPending', 'production', 'despatch', 'packed', 'complete'] as $status) {
                        $_jobSearch = new JobSearch();
                        $dataProvider = $_jobSearch->search(ArrayHelper::merge($searchParams, ['JobSearch' => [
                            'status' => 'job/' . $status,
                            'due_date_from' => date('Y-m-d 00:00:00', strtotime('first day of ' . date('Y-m-d'))),
                            'due_date_to' => date('Y-m-d 23:59:59', strtotime('last day of ' . date('Y-m-d'))),
                        ]]));
                        $_total = $dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price');
                        $total += $_total;
                        $attributes[] = [
                            'label' => Inflector::humanize(Inflector::underscore($status), true),
                            'value' => '$' . number_format($_total, 2),
                        ];
                    }
                    $attributes[] = [
                        'label' => Yii::t('app', 'Total'),
                        'value' => '$' . number_format($total, 2),
                    ];
                    echo DetailView::widget([
                        'model' => false,
                        'attributes' => $attributes,
                    ]);
                    ?>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Quotes Totals by Win Chance'); ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    $total = 0;
                    $attributes = [];
                    foreach (Job::optsQuoteWinChance() as $winChance => $winChanceLabel) {
                        $_jobSearch = new JobSearch();
                        $dataProvider = $_jobSearch->search(ArrayHelper::merge($searchParams, ['JobSearch' => [
                            'status' => 'job/quote',
                            'quote_win_chance' => $winChance,
                        ]]));
                        $_total = $dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price') * ($winChance / 100);
                        $total += $_total;
                        $attributes[] = [
                            'label' => $winChanceLabel,
                            'value' => '$' . number_format($_total, 2),
                        ];
                    }
                    $attributes[] = [
                        'label' => Yii::t('app', 'Total'),
                        'value' => '$' . number_format($total, 2),
                    ];
                    echo DetailView::widget([
                        'model' => false,
                        'attributes' => $attributes,
                    ]);
                    ?>
                </div>
            </div>

            <?php
            // DRAFT
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/draft']]);
            ?>
            <?= $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    //$output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'Drafts'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
                'orderBy' => ['job.created_at' => SORT_DESC],
            ]) ?>
        </div>
        <?php
        // QUOTES - 3 rows by win quote_win_chance
        foreach (Job::optsQuoteWinChance() as $quote_win_chance => $name) {
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/quote', 'quote_win_chance' => $quote_win_chance]]);
            ?>
            <div class="col-sm-3">
                <?= $this->render('/dashboard/pages/_jobs', [
                    'showColumns' => $showColumns,
                    'headerCallback' => function ($dataProvider) use ($quote_win_chance) {
                        $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                        $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price') * ($quote_win_chance / 100), 2);
                        return $output;
                    },
                    'title' => Html::a($name, ['/job/index', 'JobSearch' => $params['JobSearch']]),
                    'params' => $params,
                    'orderBy' => ['job.quote_retail_price' => SORT_DESC],
                ]) ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>