<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Company;
use app\models\search\JobSearch;
use app\models\User;
use app\widgets\JavaScript;
use cornernote\shortcuts\Y;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap\Tabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Production');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

$searchParams = [];

$tabs = [];
$tabs[] = [
    'label' => Yii::t('app', 'All'),
    'content' => '',
    'linkOptions' => ['class' => 'tab-all'],
    //'active' => true,
];
?>

<div class="dashboard-production">

    <?php
    // overdue
    $time = strtotime('yesterday');
    $params = ['JobSearch' => [
        'despatch_date' => '<=' . date('Y-m-d', $time),
        'status' => [
            'job/production',
            'job/despatch',
            'job/packed',
        ],
    ]];
    $jobSearch = new JobSearch;
    $jobSearch->load($_GET);
    $dataProvider = $jobSearch->search($params);
    if ($dataProvider->totalCount) {
        $tabs[] = [
            'label' => Yii::t('app', 'Overdue'),
            'content' => $this->render('_production_jobs', [
                'title' => Yii::$app->formatter->asDate($time, 'full') . ' <small>' . Yii::t('app', 'and Earlier') . '</small>',
                'params' => $params,
                'dataProvider' => $dataProvider,
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_production_jobs',
                        'heading' => Yii::t('app', 'Overdue'),
                        'params' => [
                            'params' => $params,
                            'print' => 1,
                        ],
                    ], ['target' => '_blank']);
                },
            ]),
            //'active' => true,
        ];
    }
    ?>

    <?php
    // next 7 days
    for ($day = 0; $day < 7; $day++) {
        $time = strtotime('+' . $day . ' days');
        $params = ['JobSearch' => [
            'despatch_date' => date('Y-m-d', $time),
            'status' => [
                'job/production',
                'job/despatch',
                'job/packed',
            ],
        ]];
        $jobSearch = new JobSearch;
        $jobSearch->load($_GET);
        $dataProvider = $jobSearch->search($params);
        if ($dataProvider->totalCount) {
            $title = Yii::$app->formatter->asDate($time, 'full');
            $tabs[] = [
                'label' => Yii::t('app', Yii::$app->formatter->asDate($time)),
                'content' => $this->render('_production_jobs', [
                    'title' => $title,
                    'params' => $params,
                    'dataProvider' => $dataProvider,
                    'headerCallback' => function ($dataProvider) use ($params, $title) {
                        return Html::a('<span class="fa fa-print"></span>', [
                            '/dashboard/print',
                            'view' => '_production_jobs',
                            'heading' => $title,
                            'params' => [
                                'params' => $params,
                                'print' => 1,
                            ],
                        ], ['target' => '_blank']);
                    },
                ]),
            ];
        }
    }
    ?>

    <?php
    // upcomming
    $time = strtotime('+7 days');
    $params = ['JobSearch' => [
        'despatch_date' => '>=' . date('Y-m-d', $time),
        'status' => [
            'job/production',
            'job/despatch',
            'job/packed',
        ],
    ]];
    $jobSearch = new JobSearch;
    $jobSearch->load($_GET);
    $dataProvider = $jobSearch->search($params);
    if ($dataProvider->totalCount) {
        $tabs[] = [
            'label' => Yii::t('app', 'Upcoming'),
            'content' => $this->render('_production_jobs', [
                'title' => Yii::$app->formatter->asDate($time, 'full') . ' <small>' . Yii::t('app', 'and Later') . '</small>',
                'params' => $params,
                'dataProvider' => $dataProvider,
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_production_jobs',
                        'heading' => Yii::t('app', 'Upcoming'),
                        'params' => [
                            'params' => $params,
                            'print' => 1,
                        ],
                    ], ['target' => '_blank']);
                },
            ]),
        ];
    }
    ?>


    <div id="job-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="job-searchModalLabel" aria-hidden="true">
        <?php
        $searchModel = new JobSearch();
        $searchModel->load($_GET);
        $form = ActiveForm::begin([
            'method' => 'get',
            'type' => ActiveForm::TYPE_HORIZONTAL,
        ]);
        ?>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="job-searchModalLabel">
                        <i class="fa fa-search"></i>
                        <?= Yii::t('app', 'Filter') ?>
                    </h4>
                </div>
                <div class="modal-body">
                    <?php
                    echo $form->field($searchModel, 'staff_id')->widget(Select2::className(), [
                        'model' => $searchModel,
                        'attribute' => 'staff_id',
                        'data' => ArrayHelper::map(User::find()->all(), 'id', 'label'),
                        'options' => [
                            'multiple' => false,
                            'theme' => 'krajee',
                            'placeholder' => '',
                            'language' => 'en-US',
                            'width' => '100%',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]);
                    echo $form->field($searchModel, 'name');
                    echo $form->field($searchModel, 'company_id')->widget(Select2::className(), [
                        'model' => $searchModel,
                        'attribute' => 'company_id',
                        'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $searchModel->company_id])->all(), 'id', 'name'),
                        'options' => [
                            'multiple' => false,
                            'theme' => 'krajee',
                            'placeholder' => '',
                            'language' => 'en-US',
                            'width' => '100%',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 2,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                            ],
                            'ajax' => [
                                'url' => Url::to(['company/json-list']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ],
                    ]);
                    echo $form->field($searchModel, 'shippingAddress__state');
                    ?>
                </div>
                <div class="modal-footer">
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php
    $links = [];
    if (Y::GET('JobSearch')) {
        $links[] = Html::a(Yii::t('app', 'All Jobs'), ['/dashboard/production'], [
            'class' => 'btn btn-default',
        ]);
    } else {
        $links[] = Html::a(Yii::t('app', 'My Jobs'), ['/dashboard/production', 'JobSearch' => ['staff_id' => Y::user()->id]], [
            'class' => 'btn btn-default',
        ]);
    }
    $links[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Filter'), [
        'class' => 'btn btn-default',
        'data-toggle' => 'modal',
        'data-target' => '#job-searchModal',
    ]);
    echo Html::tag('div', implode(' ', $links), ['class' => 'pull-right']);
    echo Tabs::widget([
        'id' => 'relation-tabs',
        'encodeLabels' => false,
        'items' => $tabs,
        'navType' => 'nav-pills',
    ]);
    ?>

    <?php JavaScript::begin() ?>
    <script>
        $('.tab-all').on('click', function () {
            setTimeout(function () {
                $('.tab-pane').addClass('active in');
            }, 100);
        }).click();
    </script>
    <?php JavaScript::end() ?>

</div>