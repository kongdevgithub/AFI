<?php

use app\models\Address;
use app\models\search\JobSearch;
use kartik\grid\GridView;
use app\models\Job;
use yii\helpers\Html;
use yii\web\View;


/**
 * @var View $this
 * @var string $title
 * @var array $params
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

$title = isset($title) ? $title : '';
$print = isset($print) ? $print : false;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$headerCallback = isset($headerCallback) ? $headerCallback : false;

$jobSearch = new JobSearch;
if (!$dataProvider) {
    $dataProvider = $jobSearch->search($params);
}
$dataProvider->query->joinWith('company');
$dataProvider->sort->defaultOrder = ['despatch_date' => SORT_ASC];
$dataProvider->pagination->defaultPageSize = 1000;

$cacheKey = '/dashboard/pages/_jobs_overview/' . md5(serialize([
        'title' => $title,
        'params' => $params,
    ])) . '/';

$columns = [];
//$columns[] = [
//    'class' => 'kartik\grid\ExpandRowColumn',
//    'value' => function ($model, $key, $index, $column) {
//        return GridView::ROW_COLLAPSED;
//    },
//    'detail' => function ($model, $key, $index, $column) {
//        /** @var $model Job */
//        return $this->render('_jobs-expand_items', [
//            'model' => $model,
//            'showColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status'],
//        ]);
//    },
//    'detailRowCssClass' => '',
//    'allowBatchToggle' => false,
//    'expandOneOnly' => false,
//    'contentOptions' => ['style' => 'width:5%;'],
//];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) use ($print) {
        /** @var Job $model */
        if ($print) {
            $items = [];
            if (in_array($model->status, ['job/production', 'job/despatch', 'job/packed'])) {
                foreach ($model->getStatusList() as $status => $quantity) {
                    $items[] = $status . ' x' . $quantity;
                }
            } else {
                $items[] = $model->status;
            }
            return Html::ul($items, ['class' => 'small list-unstyled']);
        }
        return $model->getStatusButtons();
    },
    'contentOptions' => ['style' => 'width:10%', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'icons',
    'format' => 'raw',
    'contentOptions' => ['style' => 'width:10%'],
];
$columns[] = [
    'attribute' => 'name',
    'value' => function ($model) use ($print) {
        /** @var Job $model */
        if ($print) {
            return implode('<br>', [
                Html::a('#' . $model->vid . ': ' . $model->name, ['/job/view', 'id' => $model->id], [
                    'class' => $model->getDateClass(),
                    'style' => 'font-weight:bold',
                ]),
                Html::a($model->company->name, ['/company/preview', 'id' => $model->company->id], [
                    'class' => 'modal-remote',
                ]),
            ]);
        }
        return Html::a('#' . $model->vid . ': ' . $model->name, ['/job/view', 'id' => $model->id], [
            'class' => $model->getDateClass(),
            'style' => 'font-weight:bold',
        ]);
    },
    'contentOptions' => ['style' => 'width:25%', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
if (!$print) {
    $columns[] = [
        'attribute' => 'company_id',
        'value' => function ($model) use ($print) {
            /** @var Job $model */
            return Html::a($model->company->name, ['/company/preview', 'id' => $model->company->id], [
                'class' => 'modal-remote',
            ]);
        },
        'contentOptions' => ['style' => 'width:25%', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
$columns[] = [
    'label' => Yii::t('app', 'State'),
    'value' => function ($model) {
        /** @var Job $model */
        return $model->getShippingStates();
    },
    'format' => 'raw',
    'enableSorting' => false,
    'contentOptions' => ['style' => 'width:10%'],
];
$columns[] = [
    'label' => Yii::t('app', 'Dates'),
    'value' => function ($model) {
        /** @var Job $model */
        $dates = [];
        $dates[] = 'despatch: ' . Yii::$app->formatter->asDate($model->despatch_date);
        if ($model->prebuild_days) {
            $dates[] = 'prebuild: ' . Yii::$app->formatter->asDate($model->prebuild_date);
        }
        $dates[] = 'due: ' . Yii::$app->formatter->asDate($model->due_date);
        if ($model->installation_date) {
            $dates[] = 'installation: ' . Yii::$app->formatter->asDate($model->installation_date);
        }
        return implode('<br>', $dates);
    },
    'format' => 'raw',
    'enableSorting' => false,
    'contentOptions' => ['style' => 'width:10%'],
    'hAlign' => 'right',
];
if (!$print) {
    $columns[] = [
        'label' => Yii::t('app', 'PM2'),
        'value' => function ($model) use ($cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/PM2';
            $output = $model->getCache($_cacheKey);
            if ($output !== false) {
                return $output;
            }
            $size = '';
            $area = $model->getArea();
            if ($area) {
                $size = ceil($area) . 'm<sup>2</sup>';
            }
            return $model->setCache($_cacheKey, $size);
        },
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:5%'],
        'hAlign' => 'right',
    ];
    $columns[] = [
        'label' => Yii::t('app', 'FM'),
        'value' => function ($model) use ($cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/FM';
            $output = $model->getCache($_cacheKey);
            if ($output !== false) {
                return $output;
            }
            $size = '';
            $perimeter = $model->getPerimeter();
            if ($perimeter) {
                $size = ceil($perimeter) . 'm';
            }
            return $model->setCache($_cacheKey, $size);
        },
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:5%'],
        'hAlign' => 'right',
    ];
    $columns[] = [
        'label' => Yii::t('app', 'Units'),
        'value' => function ($model) use ($cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/units';
            $output = $model->getCache($_cacheKey);
            if ($output) {
                return $output;
            }
            $productCount = $model->getProducts()->count();
            $itemCount = 0;
            $unitCount = 0;
            foreach ($model->products as $product) {
                $itemCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->count();
                $unitCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->sum('item.quantity') * $product->quantity;
            }
            return $model->setCache($_cacheKey, implode('&nbsp;|&nbsp;', [$productCount, $itemCount, $unitCount]));
        },
        'format' => 'raw',
        'contentOptions' => ['style' => 'width:5%'],
    ];
}

$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

echo GridView::widget([
    'id' => $grid_id,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    //'filterModel' => $jobSearch,
    //'showPageSummary' => true,
    'columns' => $columns,
    'tableOptions' => [
        'class' => 'no-margin',
    ],
    'striped' => false,
    'condensed' => true,
    'bordered' => false,
    'showHeader' => false,
    'panel' => $print ? false : [
        'heading' => $title,
        'footer' => false,
        'after' => false,
        'before' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => ($panelHeadingExtra ? '<div class="pull-right">' . $panelHeadingExtra . '</div>' : '') . '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
]);
