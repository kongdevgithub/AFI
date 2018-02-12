<?php
/* @var $this yii\web\View */

use app\models\Item;
use app\models\ItemType;
use app\models\Job;
use app\models\search\ItemSearch;
use app\models\search\JobSearch;
use app\modules\client\components\MenuItem;
use app\widgets\Nav;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

$this->title = Yii::$app->name;
$this->params['heading'] = false;

$items = MenuItem::getJobsItems();
?>

<div class="site-index">
    <div class="row row-md-3-clear">
        <div class="col-md-3">

            <div class="box" style="font-size: 150%;">
                <div class="box-header with-border">
                    <h3 class="box-title" style="font-size: 150%;"><?= Html::tag('span', '', ['class' => 'icon fa fa-folder']) . ' ' . Yii::t('app', 'Jobs') ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    foreach ($items as &$v) {
                        unset($v['items']);
                    }
                    echo Nav::widget([
                        'options' => ['class' => 'list-unstyled'],
                        'encodeLabels' => false,
                        'items' => $items,
                    ]);
                    ?>
                </div>
            </div>

            <?php
            // Artwork Approval Required
            $params = ['ItemSearch' => [
                'job__client_company_id' => Yii::$app->user->identity->getClientCompanies(),
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-print/approval',
                'quantity' => '>0',
            ]];
            $itemSearch = new ItemSearch;
            $dataProvider = $itemSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            $jobs = [];
            foreach ($dataProvider->getModels() as $item) {
                /** @var Item $item */
                if (!isset($jobs[$item->product->job_id])) {
                    $jobs[$item->product->job_id] = $item->product->job_id;
                }
            }
            $params = [
                'JobSearch' => [
                    'id' => $jobs ?: 'fake',
                ],
            ];

            $jobSearch = new JobSearch;
            $dataProvider = $jobSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            $dataProvider->query->orderBy('job.prebuild_date ASC');
            echo GridView::widget([
                'layout' => '{items}',
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {
                            /** @var Job $model */
                            $output = [];
                            $name = [];
                            $name[] = '#' . $model->vid . ': ' . $model->name;
                            $title = [];
                            $title[] = Html::a(implode('<br>', $name), ['/approval/artwork', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))], [
                                'class' => $model->getDateClass(),
                                'style' => 'font-weight:bold',
                            ]);
                            $output[] = implode(' ', $title);
                            $dates = [];
                            if ($model->status == 'job/draft') {
                                $dates[] = 'created: ' . Yii::$app->formatter->asDate($model->created_at);
                            } else {
                                if ($model->status == 'job/quote') {
                                    if ($model->quote_at) {
                                        $dates[] = 'quote: ' . Yii::$app->formatter->asDate($model->quote_at);
                                    }
                                } else {
                                    if ($model->prebuild_days) {
                                        $dates[] = 'prebuild: ' . Yii::$app->formatter->asDate($model->prebuild_date);
                                    }
                                    $dates[] = 'despatch: ' . Yii::$app->formatter->asDate($model->despatch_date);
                                    //$dates[] = 'due: ' . Yii::$app->formatter->asDate($model->due_date);
                                }
                            }
                            $info = Html::tag('small', implode('<br>', $dates));
                            $output[] = $info;
                            return implode('<br>', $output);
                        },
                        'format' => 'raw',
                        'enableSorting' => false,
                        //'contentOptions' => ['width' => '50%'],
                    ],
                ],
                'tableOptions' => [
                    'class' => 'no-margin',
                ],
                'striped' => false,
                'condensed' => true,
                'bordered' => false,
                'showHeader' => false,
                'panel' => [
                    'heading' => Html::a(Yii::t('app', 'Artwork Approval Required'), ['job/index', 'JobSearch' => $params['JobSearch']]),
                    'footer' => false,
                    'after' => false,
                    'before' => false,
                    'type' => GridView::TYPE_DEFAULT,
                ],
                'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
            ]);
            ?>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Upload Artwork') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <iframe src="https://spaces.hightail.com/uplink/AFIBrandingSolutions" frameborder="0" width="100%" height="550"></iframe>
                </div>
            </div>

        </div>
        <div class="col-md-3">
            <?php
            // Drafts
            $searchParams = [];
            $searchParams['JobSearch']['client_company_id'] = Yii::$app->user->identity->getClientCompanies();
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/draft']]);
            ?>
            <?= $this->render('//dashboard/pages/_jobs', [
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.name.staff', 'name.dates', 'report_total', 'status'],
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    //$output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'Drafts'), ['job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
                'orderBy' => ['job.created_at' => SORT_DESC],
            ]) ?>
        </div>
        <div class="col-md-3">
            <?php
            // Quotes
            $searchParams = [];
            $searchParams['JobSearch']['client_company_id'] = Yii::$app->user->identity->getClientCompanies();
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => 'job/quote']]);
            ?>
            <?= $this->render('//dashboard/pages/_jobs', [
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.name.staff', 'name.dates', 'report_total', 'status'],
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    //$output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'Quotes'), ['job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
                'orderBy' => ['job.created_at' => SORT_DESC],
            ]) ?>
        </div>
        <div class="col-md-3">
            <?php
            // Jobs
            $searchParams = [];
            $searchParams['JobSearch']['client_company_id'] = Yii::$app->user->identity->getClientCompanies();
            $params = ArrayHelper::merge($searchParams, ['JobSearch' => ['status' => ['job/productionPending', 'job/production', 'job/production', 'job/despatch', 'job/packed']]]);
            ?>
            <?= $this->render('//dashboard/pages/_jobs', [
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.name.staff', 'name.dates', 'report_total', 'status'],
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    //$output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'Jobs'), ['job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
                'orderBy' => ['job.created_at' => SORT_DESC],
            ]) ?>
        </div>
    </div>
</div>

