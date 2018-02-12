<?php

use app\components\Helper;
use app\models\search\JobSearch;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use app\models\Job;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;


/**
 * @var View $this
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var int $pageSize
 */

$title = isset($title) ? $title : false;
$orderBy = isset($orderBy) ? $orderBy : ['job.prebuild_date' => SORT_ASC, 'job.id' => SORT_ASC];
$pageSize = isset($pageSize) ? $pageSize : 1000;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$params = isset($params) ? $params : [];
$showColumns = isset($showColumns) ? $showColumns : ['name', 'name.name'];
$headerCallback = isset($headerCallback) ? $headerCallback : false;
$progressUnitStatus = isset($progressUnitStatus) ? $progressUnitStatus : false;
$progressUnitItemType = isset($progressUnitItemType) ? $progressUnitItemType : false;
$progressItemStatus = isset($progressItemStatus) ? $progressItemStatus : false;
$progressItemItemType = isset($progressItemItemType) ? $progressItemItemType : false;
$expandItemsItemType = isset($expandItemsItemType) ? $expandItemsItemType : false;
$expandItemsShowColumns = isset($expandItemsShowColumns) ? $expandItemsShowColumns : ['name', 'name.name'];
$expandItemsUnitStatus = isset($expandItemsUnitStatus) ? $expandItemsUnitStatus : null;
$expandItemsAjax = isset($expandItemsAjax) ? $expandItemsAjax : true;

$jobSearch = new JobSearch;
if (!$dataProvider) {
    $dataProvider = $jobSearch->search($params);
}
if ($pageSize) {
    $dataProvider->pagination->pageSize = $pageSize;
}
if ($orderBy) {
    $dataProvider->query->orderBy($orderBy);
}

$cacheKey = '/dashboard/pages/_jobs/' . md5(serialize([
        'showColumns' => $showColumns,
        'progressUnitStatus' => $progressUnitStatus,
        'progressUnitItemType' => $progressUnitItemType,
        'progressItemStatus' => $progressItemStatus,
        'progressItemItemType' => $progressItemItemType,
    ])) . '/';

$columns = [];
if (in_array('expand_items', $showColumns)) {
    $columns[] = [
        'class' => 'kartik\grid\ExpandRowColumn',
        'value' => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail' => !$expandItemsAjax ? function ($model, $key, $index, $column) use ($expandItemsItemType, $expandItemsShowColumns, $expandItemsUnitStatus) {
            /** @var $model Job */
            return $this->render('_jobs-expand-items', [
                'model' => $model,
                'item_type_id' => $expandItemsItemType,
                'includeUnitStatus' => $expandItemsUnitStatus,
                'showColumns' => $expandItemsShowColumns,
            ]);
        } : null,
        'detailUrl' => $expandItemsAjax ? Url::to([
            'dashboard/jobs-expand-items',
            'item_type_id' => $expandItemsItemType,
            'includeUnitStatus' => $expandItemsUnitStatus,
            'showColumns' => $expandItemsShowColumns,
            'ru' => ReturnUrl::getToken(),
        ]) : null,
        'enableCache' => false,

        'detailRowCssClass' => '',
        'allowBatchToggle' => false,
        'expandOneOnly' => false,
    ];
}
if (in_array('status', $showColumns)) {
    $columns[] = [
        'attribute' => 'status',
        'value' => function ($model) {
            /** @var Job $model */
            return $model->getStatusButtons();
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('name', $showColumns)) {
    $columns[] = [
        'attribute' => 'name',
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/name';
            //$output = $model->getCache($_cacheKey);
            $output = false;
            if (!$output) {
                $output = [];

                // name
                if (in_array('name.name', $showColumns)) {
                    $name[] = Html::a($model->name, ['/job/view', 'id' => $model->id], [
                        'class' => $model->getDateClass(),
                        'style' => 'font-weight:bold',
                    ]);
                    $name[] = Html::tag('small', $model->company->name);
                    $output[] = implode('<br>', $name);
                }


//                if (in_array('name.name', $showColumns)) {
////                    $name = [];
////                    $name[] = '#' . $model->vid . ': ' . $model->name;
//                    if (in_array('name.name.staff', $showColumns)) {
//                        $title[] = Html::a($model->staffRep->getAvatar(), ['/user/profile/show', 'id' => $model->staffRep->id], ['class' => 'modal-remote']);
//                        $title[] = Html::a($model->staffCsr->getAvatar(), ['/user/profile/show', 'id' => $model->staffCsr->id], ['class' => 'modal-remote']);
//                    }
//                    $output[] = implode(' ', $title);
//                }

                // dates
                $dates = '';
                if (in_array('name.dates', $showColumns)) {
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
                            if ($model->installation_date) {
                                $dates[] = 'installation: ' . Yii::$app->formatter->asDate($model->installation_date);
                            }
                        }
                    }
                    $dates = Html::tag('small', implode(' | ', $dates));
                }

                // links
                $links = '';
                if (in_array('name.links', $showColumns)) {
                    $links = [];
                    $links[] = 'j:&nbsp;' . Html::a($model->vid, ['//job/view', 'id' => $model->id]);
                    $links = Html::tag('small', implode(' | ', $links));
                }

                if ($links || $dates) {
                    if ($links && $dates) {
                        $dates .= ' | ';
                    }
                    $output[] = $dates . $links;
                }

                $output = implode('<hr style="margin: 2px 0;">', $output);
            }
            return $model->setCache($_cacheKey, $output);
        },
        'format' => 'raw',
        'enableSorting' => false,
        //'contentOptions' => ['width' => '50%'],
    ];
}
if (in_array('description', $showColumns)) {
    $columns[] = [
        'label' => Yii::t('app', 'Job'),
        'attribute' => 'description',
        'value' => function ($model) use ($cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/description';
            $output = $model->getCache($_cacheKey);
            if (!$output) {
                $output = Html::a($model->getTitle(), ['//job/view', 'id' => $model->id]);
            }
            return $model->setCache($_cacheKey, $output);
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('company_id', $showColumns)) {
    $columns[] = [
        'attribute' => 'company_id',
        'value' => function ($model) use ($cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/company_id';
            $output = $model->getCache($_cacheKey);
            if (!$output) {
                $output = Html::a($model->company->name, ['//company/view', 'id' => $model->company->id]);
            }
            return $model->setCache($_cacheKey, $output);
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('quote_retail_price', $showColumns)) {
    $columns[] = [
        'attribute' => 'quote_retail_price',
        'header' => Yii::t('app', 'Quote'),
        'format' => ['decimal', 2],
        //'pageSummary' => true,
        'contentOptions' => ['class' => 'text-right'],
        //'pageSummaryOptions' => ['class' => 'text-right'],
        'enableSorting' => false,
    ];
}
if (in_array('report_total', $showColumns)) {
    $columns[] = [
        'header' => Yii::t('app', 'Price'),
        'format' => ['decimal', 2],
        //'pageSummary' => true,
        'contentOptions' => ['class' => 'text-right'],
        //'pageSummaryOptions' => ['class' => 'text-right'],
        'value' => function ($model) {
            /** @var Job $model */
            return $model->getReportTotal();
        },
        'enableSorting' => false,
    ];
}

if (in_array('quote_win_chance', $showColumns)) {
    $columns[] = [
        'attribute' => 'quote_win_chance',
        //'header' => Yii::t('app', 'Win%'),
        'contentOptions' => ['class' => 'text-center'],
    ];
}
if (in_array('approval', $showColumns)) {
    $columns[] = [
        'header' => Yii::t('app', 'Approval'),
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/approval';
            $output = $model->getCache($_cacheKey);
            if ($output) {
                return $output;
            }
            $output = [];
            $ready = 0;
            $total = 0;
            foreach ($model->products as $product) {
                foreach ($product->items as $item) {
                    if ($item->quantity < 1) continue;
                    $status = explode('/', $item->status)[1];
                    if (in_array($status, ['artwork', 'approval'])) {
                        $quantity = $item->quantity * $item->product->quantity;
                        $total += $quantity;
                        if ($status == 'approval') {
                            $ready += $quantity;
                        }
                    }
                }
            }

            if (in_array('progress_unit.icons', $showColumns)) {
                $icons = $model->getIcons();
                if ($icons) {
                    $output[] = $icons;
                }
            }
            if (in_array('progress_unit.area', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getArea()) . 'm<sup>2</sup>');
            }
            if (in_array('progress_unit.perimeter', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getPerimeter()) . 'm');
            }
            $output[] = Html::a('<i class="fa fa-envelope"></i>', ['/job/artwork-email', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote',
                'title' => Yii::t('app', 'Artwork Email'),
                'data-toggle' => 'tooltip',
            ]);

            $output = implode(' | ', $output) . '<br>' . Helper::getProgressBarHtml($ready, $total);

            return $model->setCache($_cacheKey, $output);
        },
        'contentOptions' => ['class' => 'text-right', 'nowrap' => 'nowrap', 'style' => 'width:200px;'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('progress_unit', $showColumns) && $progressUnitItemType && $progressUnitStatus) {
    $columns[] = [
        'header' => Yii::t('app', 'Progress'),
        'value' => function ($model) use ($showColumns, $cacheKey, $progressUnitItemType, $progressUnitStatus) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/progress';
            $output = $model->getCache($_cacheKey);
            if ($output) {
                return $output;
            }
            $output = [];
            $ready = 0;
            $total = 0;
            foreach ($model->products as $product) {
                foreach ($product->items as $item) {
                    if ($progressUnitItemType && $item->item_type_id != $progressUnitItemType) continue;
                    if ($item->quantity < 1) continue;
                    foreach ($item->units as $unit) {
                        $total += $unit->quantity;
                        if ($unit->status == $progressUnitStatus) {
                            $ready += $unit->quantity;
                        }
                    }
                }
            }
            if (in_array('progress_unit.icons', $showColumns)) {
                $icons = $model->getIcons();
                if ($icons) {
                    $output[] = $icons;
                }
            }
            if (in_array('progress_unit.area', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getArea()) . 'm<sup>2</sup>');
            }
            if (in_array('progress_unit.perimeter', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getPerimeter()) . 'm');
            }
            $output[] = Html::a('<span class="fa fa-legal"></span>', ['/unit/progress', 'job_id' => $model->id, 'status' => $progressUnitStatus, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote',
                'title' => Yii::t('app', 'Progress Units'),
                'data-toggle' => 'tooltip',
            ]);
            $output = implode(' | ', $output) . '<br>' . Helper::getProgressBarHtml($ready, $total);

            return $model->setCache($_cacheKey, $output);
        },
        'contentOptions' => ['class' => 'text-right', 'nowrap' => 'nowrap', 'style' => 'width:200px;'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

if (in_array('progress_item', $showColumns) && $progressItemItemType && $progressItemStatus) {
    $columns[] = [
        'header' => Yii::t('app', 'Progress'),
        'value' => function ($model) use ($showColumns, $cacheKey, $progressItemItemType, $progressItemStatus) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/progress';
            $output = $model->getCache($_cacheKey);
            if ($output) {
                return $output;
            }
            $output = [];
            $ready = 0;
            $total = 0;
            foreach ($model->products as $product) {
                foreach ($product->items as $item) {
                    if ($progressItemItemType && $item->item_type_id != $progressItemItemType) continue;
                    if ($item->quantity < 1) continue;
                    $total += $item->quantity * $item->product->quantity;
                    if ($item->status == $progressItemStatus) {
                        $ready += $item->quantity * $item->product->quantity;
                    }
                }
            }
            if (in_array('progress_item.icons', $showColumns)) {
                $icons = $model->getIcons();
                if ($icons) {
                    $output[] = $icons;
                }
            }
            if (in_array('progress_item.area', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getArea()) . 'm<sup>2</sup>');
            }
            if (in_array('progress_item.perimeter', $showColumns)) {
                $output[] = Html::tag('strong', ceil($model->getPerimeter()) . 'm');
            }
            $output[] = Html::a('<span class="fa fa-legal"></span>', ['/item/progress', 'job_id' => $model->id, 'status' => $progressItemStatus, 'ru' => ReturnUrl::getToken()], [
                'class' => 'modal-remote',
                'title' => Yii::t('app', 'Progress Items'),
                'data-toggle' => 'tooltip',
            ]);
            $output = implode(' | ', $output) . '<br>' . Helper::getProgressBarHtml($ready, $total);

            return $model->setCache($_cacheKey, $output);
        },
        'contentOptions' => ['class' => 'text-right', 'nowrap' => 'nowrap', 'style' => 'width:200px;'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

if (in_array('size', $showColumns)) {
    $columns[] = [
        'attribute' => 'sizeHtml',
        'value' => function ($model) use ($showColumns, $cacheKey) {
            /** @var Job $model */
            $_cacheKey = $cacheKey . '/sizeHtml';
            $output = $model->getCache($_cacheKey);
            if ($output) {
                return $output;
            }
            $size = [];
            $area = $model->getArea();
            if ($area) {
                $size[] = ceil($area) . 'm<sup>2</sup>';
            }
            $perimeter = $model->getPerimeter();
            if ($perimeter) {
                $size[] = ceil($perimeter) . 'm';
            }
            $output = implode('&nbsp;|&nbsp;', $size);
            return $model->setCache($_cacheKey, $output);
        },
        'format' => 'raw',
    ];
}

if (in_array('icons', $showColumns)) {
    $columns[] = [
        'label' => Yii::t('app', 'Icons'),
        'value' => function ($model) {
            /** @var Job $model */
            return $model->getIcons();
        },
        //'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

if (in_array('created_at', $showColumns)) {
    $columns[] = [
        'attribute' => 'created_at',
        'format' => 'date',
    ];
}


$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

$panelAfter = false;
$actionButtons = false;
if (in_array('progress_unit', $showColumns) && $progressUnitItemType && $progressUnitStatus) {
    $actionButtons = implode(' ', [
        Html::a(Yii::t('app', 'Progress Units'), ['/unit/progress', 'status' => $progressUnitStatus, 'ru' => ReturnUrl::getToken()], [
            'type' => 'button',
            'title' => Yii::t('app', 'Progress'),
            'class' => 'btn btn-primary btn-xs modal-remote-form',
            'data-grid' => $grid_id,
        ]),
    ]);
    $panelAfter = true;
}
if (in_array('progress_item', $showColumns) && $progressItemItemType && $progressItemStatus) {
    $actionButtons = implode(' ', [
        Html::a(Yii::t('app', 'Progress Items'), ['/item/progress', 'status' => $progressItemStatus, 'ru' => ReturnUrl::getToken()], [
            'type' => 'button',
            'title' => Yii::t('app', 'Progress'),
            'class' => 'btn btn-primary btn-xs modal-remote-form',
            'data-grid' => $grid_id,
        ]),
    ]);
    $panelAfter = true;
}
if ($panelAfter) {
    $checkAll = Html::label(Html::checkbox('check_all', false, ['class' => 'select-on-check-all']) . ' ' . Yii::t('app', 'check all'));
    $checkAll = Html::tag('div', $checkAll, ['class' => 'checkbox']);
    $panelAfter = Html::tag('div', $checkAll, ['class' => 'pull-left']) . Html::tag('div', $actionButtons, ['class' => 'pull-right']);
    $panelAfter = Html::tag('div', $panelAfter, ['class' => 'clearfix']);
    if ($dataProvider->totalCount) {
        $this->registerJs("jQuery('#$grid_id').yiiGridView('setSelectionColumn', " . Json::encode([
                'name' => 'check',
                'multiple' => true,
                'checkAll' => 'check_all',
            ]) . ");");
    }
}

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
    'panel' => [
        'heading' => $title,
        'footer' => false,
        'after' => $panelAfter,
        'before' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => ($panelHeadingExtra ? '<div class="pull-right">' . $panelHeadingExtra . '</div>' : '') . '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
]);
