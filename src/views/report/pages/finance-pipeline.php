<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\search\JobSearch;
use app\models\User;
use app\widgets\Nav;
use cornernote\shortcuts\Y;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'Finance Pipeline');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$showColumns = [
    'name',
    'name.name',
    'name.name.company',
    'name.links',
    'name.dates',
    'report_total',
];
$headerCallback = function ($dataProvider) {
    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
    $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
    return $output;
};

$searchParams = ['JobSearch' => isset($_GET['JobSearch']) ? $_GET['JobSearch'] : []];
$searchParams['JobSearch']['invoice_sent'] = 0;

$staffReps = User::find()->byRole('rep')->all();
$staff_rep_id = Y::GET('staff_rep_id') ?: Yii::$app->user->id;
if (!$staff_rep_id || !in_array($staff_rep_id, ArrayHelper::map($staffReps, 'id', 'id'))) {
    $staff_rep_id = 'all';
}
$staffRep = $staff_rep_id != 'all' ? User::findOne($staff_rep_id) : false;
if ($staff_rep_id != 'all') {
    $searchParams['JobSearch']['staff_rep_id'] = $staff_rep_id;
}

?>

<div class="report-finance-production">

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
                // dates
                echo '<h4>Dates</h4>';
                $jobSearch = new JobSearch();
                $jobSearch->load($searchParams);
                $form = ActiveForm::begin([
                    'method' => 'get',
                ]);
                echo $form->field($jobSearch, 'despatch_date_to')->widget(DatePicker::className(), [
                    'layout' => '{picker}{input}',
                    'options' => ['class' => 'form-control'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                        //'orientation' => 'top left',
                    ],
                ]);
                echo Html::submitButton(Yii::t('app', 'Refresh'), ['class' => 'btn btn-success']);
                ActiveForm::end();

                // staff_rep
                if (Yii::$app->user->can('manager')) {
                    echo '<h4>Staff Rep</h4>';
                    $items = [];
                    $items[] = [
                        'label' => '-ALL-',
                        'url' => ['/report/index', 'report' => 'finance-pipeline', 'staff_rep_id' => 'all'],
                        'active' => !$staffRep,
                    ];
                    foreach (User::find()->byRole('rep')->all() as $_staffRep) {
                        $_searchParams = $searchParams;
                        $_searchParams['JobSearch']['staff_rep_id'] = $_staffRep->id;

                        $totals = [];
                        $_jobSearch = new JobSearch();
                        $dataProvider = $_jobSearch->search(ArrayHelper::merge($_searchParams, ['JobSearch' => [
                            'status' => ['job/productionPending', 'job/production', 'job/despatch', 'job/packed'],
                        ]]));
                        $totals['production'] = $dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price');

                        $_jobSearch = new JobSearch();
                        $dataProvider = $_jobSearch->search(ArrayHelper::merge($_searchParams, ['JobSearch' => [
                            'status' => ['job/complete'],
                        ]]));
                        $totals['uninvoiced'] = $dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price');

                        $total = $totals['production'] + $totals['uninvoiced'];
                        if ($total == 0) {
                            continue;
                        }

                        $items[sprintf('%20d', $total) . '-' . uniqid()] = [
                            'label' => Html::tag('strong', $_staffRep->label) . ' ' . Html::tag('span', '$' . number_format($total, 2), ['class' => 'label label-default']),
                            'url' => ['/report/index', 'report' => 'finance-pipeline', 'staff_rep_id' => $_staffRep->id, 'JobSearch' => $searchParams['JobSearch']],
                            'active' => $staffRep && $_staffRep->id == $staffRep->id,
                        ];
                    }
                    krsort($items);
                    echo Nav::widget([
                        'encodeLabels' => false,
                        'options' => ['class' => 'nav-pills nav-stacked'],
                        'items' => $items,
                    ]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-md-3">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Uninvoiced Totals by Job Status'); ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    $total = 0;
                    $attributes = [];
                    foreach (['productionPending', 'production', 'despatch', 'packed', 'complete'] as $status) {
                        $_jobSearch = new JobSearch();
                        $dataProvider = $_jobSearch->search(ArrayHelper::merge($searchParams, ['JobSearch' => [
                            'status' => 'job/' . $status,
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


            <?php
            // Production Pending
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/productionPending']]);
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => $headerCallback,
                'title' => Html::a(Yii::t('app', 'Production Pending'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?php
            // Production
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/production']]);
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => $headerCallback,
                'title' => Html::a(Yii::t('app', 'Production'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?php
            // Despatch
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => ['job/despatch']]]);
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => $headerCallback,
                'title' => Html::a(Yii::t('app', 'Despatch'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>
            <?php
            // Packed
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/packed']]);
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => $headerCallback,
                'title' => Html::a(Yii::t('app', 'Packed'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?php
            // Complete (not Invoiced)
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/complete']]);
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => $headerCallback,
                'title' => Html::a(Yii::t('app', 'Complete'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>
        </div>
    </div>

</div>