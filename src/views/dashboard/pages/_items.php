<?php

use app\components\ReturnUrl;
use app\components\SortableGridView;
use app\models\Item;
use app\models\ItemType;
use app\models\MachineType;
use app\models\Option;
use app\models\query\ItemQuery;
use app\models\search\ItemSearch;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;


/**
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var array $showColumns
 */

$ru = ReturnUrl::getRequestToken() ?: ReturnUrl::getToken();

$itemSearch = new ItemSearch;
$dataProvider = $itemSearch->search(isset($params) ? $params : []);
$title = isset($title) ? $title : false;
$orderBy = isset($orderBy) ? $orderBy : 'job.prebuild_date ASC, job.id ASC, item.id ASC';
$pageSize = isset($pageSize) ? $pageSize : 1000;
$headerCallback = isset($headerCallback) ? $headerCallback : false;
$includeUnitStatus = isset($includeUnitStatus) ? (is_array($includeUnitStatus) ? $includeUnitStatus : [$includeUnitStatus]) : null;
$progressUnitStatus = isset($progressUnitStatus) ? $progressUnitStatus : false;
$progressUnitItemType = isset($progressUnitItemType) ? $progressUnitItemType : false;
$progressItemStatus = isset($progressItemStatus) ? $progressItemStatus : false;
$progressItemItemType = isset($progressItemItemType) ? $progressItemItemType : false;
$showColumns = isset($showColumns) ? $showColumns : ['name'];
$sortModel = isset($sortModel) ? $sortModel : false;

/** @var ItemQuery $query */
$query = $dataProvider->query;
$query->joinWith('product.job');

if ($sortModel) {
    $query->leftJoin('sort_order', 'sort_order.model_name = :model_name AND sort_order.model_id = item.id', [
        'model_name' => $sortModel,
    ]);
    $query->orderBy('sort_order.sort_order DESC');
    $dataProvider->sort->defaultOrder = 'sort_order.sort_order DESC';
} elseif ($orderBy) {
    $query->orderBy($orderBy);
    $dataProvider->sort->defaultOrder = $orderBy;
}

if ($pageSize) {
    $dataProvider->pagination->pageSize = $pageSize;
}

$columns = [];

if (in_array('sortable', $showColumns) || in_array('machine', $showColumns)) {
    $columns[] = [
        'label' => 'sortable',
        'value' => function ($model) {
            /** @var Item $model */
            return '<i class="fa fa-arrows sortable-handle"></i>';
        },
        'format' => 'raw',
    ];
}

if (in_array('description', $showColumns)) {
    $columns[] = [
        'label' => 'description',
        'value' => function ($model) use ($showColumns) {
            /** @var Item $model */
            return $model->getDescription([
                'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                'ignoreOptions' => [Option::OPTION_ARTWORK],
                'listOptions' => ['class' => 'list-unstyled'],
            ]);
        },
        'format' => 'raw',
    ];
}

if (in_array('company_id', $showColumns)) {
    $columns[] = [
        'label' => 'company_id',
        'value' => function ($model) use ($showColumns) {
            /** @var Item $model */
            return Html::a($model->product->job->company->name, ['//company/view', 'id' => $model->product->job->company->id]);
        },
        'format' => 'raw',
    ];
}

if (in_array('status', $showColumns)) {
    $columns[] = [
        'label' => 'status',
        'value' => function ($model) use ($ru, $showColumns, $includeUnitStatus) {
            /** @var Item $model */
            $job = $model->product->job;

            // status
            $status = '';
            if (in_array('status.checkbox', $showColumns)) {
                $status .= Html::checkbox('check') . ' ';
            }
            $status .= $model->getStatusButtons(false, $includeUnitStatus) . '<hr style="margin: 2px 0;">';

            // icons
            $icons = '';
            if (in_array('status.icons', $showColumns)) {
                if (in_array('status.icons.print_tag', $showColumns)) {
                    $icons = $model->getPrintTagIcon() . ' ';
                }
                $icons .= $model->getIcons();
                if ($icons) {
                    $icons .= '<hr style="margin: 2px 0;">';
                }
            }

            // artwork
            $artwork = '';
            if (in_array('status.artwork', $showColumns)) {
                if ($model->artwork) {
                    $thumb = Html::img($model->artwork->getFileUrl('100x100'));
                    if (Y::user()->can('app_item_artwork', ['route' => true])) {
                        $artwork = Html::a($thumb, $model->getUrl('artwork', ['ru' => $ru]), ['class' => 'modal-remote']);
                    } else {
                        $artwork = Html::a($thumb, $model->artwork->getFileUrl('800x800'), ['data-fancybox' => 'gallery-' . $model->artwork->id]);
                    }
                } else {
                    if (Y::user()->can('app_item_artwork', ['route' => true])) {
                        $artwork = Html::a('<i class="fa fa-upload"></i>', ['/item/artwork', 'id' => $model->id, 'ru' => $ru], [
                            'class' => 'modal-remote',
                            'title' => Yii::t('app', 'Artwork'),
                            'data-toggle' => 'tooltip',
                        ]);
                    }
                }
            }

            // dates
            $dates = '';
            if (in_array('status.dates', $showColumns)) {
                $dates = [];
                if (in_array('status.job_dates', $showColumns)) {
                    $dates[] = 'j:&nbsp;' . Yii::$app->formatter->asDate($job->prebuild_date);
                }
                if ($model->product->due_date && $model->product->due_date != $job->due_date) {
                    $dates[] = 'p:&nbsp;' . Yii::$app->formatter->asDate($model->product->due_date);
                }
                if ($model->due_date && $model->due_date != $model->product->due_date) {
                    $dates[] = 'i:&nbsp;' . Yii::$app->formatter->asDate($model->due_date);
                }
                $dates = '<hr style="margin: 2px 0;">' . Html::tag('small', implode('<br>', $dates));
            }

            // links
            $links = '';
            if (in_array('status.links', $showColumns)) {
                $links = [];
                if (in_array('status.links.job', $showColumns)) {
                    $links[] = 'j:&nbsp;' . Html::a($job->id, ['//job/production', 'id' => $job->id]);
                }
                $links[] = 'p:&nbsp;' . Html::a($model->product->id, ['//product/view', 'id' => $model->product->id]);
                $links[] = 'i:&nbsp;' . Html::a($model->id, ['//item/view', 'id' => $model->id]);
                $links = '<hr style="margin: 2px 0;">' . Html::tag('small', implode('<br>', $links));
            }

            // return
            return $status . $icons . $artwork . $links . $dates;
        },
        'format' => 'raw',
    ];
}

if (in_array('name', $showColumns)) {
    $columns[] = [
        'label' => 'name',
        'value' => function ($model) use ($ru, $showColumns) {
            /** @var Item $model */
            $job = $model->product->job;

            // name
            $name = [];
            if (in_array('name.job_name', $showColumns)) {
                $name[] = Html::a($job->name, ['/job/view', 'id' => $job->id], [
                    'class' => $job->getDateClass(),
                    'style' => 'font-weight:bold',
                ]);
                $name[] = Html::tag('small', $job->company->name);
            }
            if (in_array('name.name', $showColumns)) {
                $name[] = Html::tag('div', $model->name . ' | ' . $model->product->name, [
                    'class' => 'small',
                    'style' => 'font-weight:bold'
                ]);
            }
            $name = implode('<br>', $name) . '<hr style="margin: 2px 0;">';

            // dates
            $dates = '';
            if (in_array('name.dates', $showColumns)) {
                $dates = [];
                if (in_array('name.dates.job', $showColumns)) {
                    $dates[] = 'j:&nbsp;' . Yii::$app->formatter->asDate($job->prebuild_date);
                }
                if ($model->product->due_date && $model->product->due_date != $job->due_date) {
                    $dates[] = 'p:&nbsp;' . Yii::$app->formatter->asDate($model->product->due_date);
                }
                if ($model->due_date && $model->due_date != $model->product->due_date) {
                    $dates[] = 'i:&nbsp;' . Yii::$app->formatter->asDate($model->due_date);
                }
                $dates = $dates ? Html::tag('small', implode(' | ', $dates)) : '';
            }

            // links
            $links = '';
            if (in_array('name.links', $showColumns)) {
                $links = [];
                if (in_array('name.links.job', $showColumns)) {
                    $links[] = 'j:&nbsp;' . Html::a($job->id, ['//job/view', 'id' => $job->id]);
                }
                $links[] = 'p:&nbsp;' . Html::a($model->product->id, ['//product/view', 'id' => $model->product->id]);
                $links[] = 'i:&nbsp;' . Html::a($model->id, ['//item/view', 'id' => $model->id]);
                $links = Html::tag('small', implode(' | ', $links));
                if ($dates) {
                    $links = ' | ' . $links;
                }
            }
            if ($links || $dates) {
                $links .= '<hr style="margin: 2px 0;">';
            }

            // description
            $description = '';
            if (in_array('name.description', $showColumns)) {
                $description = $model->getDescription([
                    'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                    'ignoreOptions' => [Option::OPTION_ARTWORK],
                    'listOptions' => ['class' => 'list-unstyled'],
                ]);
            }

            // area
            $area = '';
            if (in_array('name.area', $showColumns)) {
                $area = $model->getArea();
                if ($area) {
                    $area = Html::tag('span', ceil($area) . 'm<sup>2</sup>', ['class' => 'label label-default']) . ' ';
                } else {
                    $area = '';
                }
            }

            // perimeter
            $perimeter = '';
            if (in_array('name.perimeter', $showColumns)) {
                $perimeter = $model->getPerimeter();
                if ($perimeter) {
                    $perimeter = Html::tag('span', ceil($perimeter) . 'm', ['class' => 'label label-default']) . ' ';
                } else {
                    $perimeter = '';
                }
            }

            // size
            $size = '';
            if (in_array('name.size', $showColumns)) {
                $sizeHtml = $model->getSizeHtml();
                if ($sizeHtml) {
                    $size = Html::tag('span', str_replace(' ', '&nbsp;', $model->getSizeHtml()), ['class' => 'label label-default']) . '<br>';
                }
            }

            // machine
            $machine = '';
            if (in_array('name.machine', $showColumns)) {
                $link = '';
                if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                    if (Y::user()->can('app_item_printer', ['route' => true])) {
                        $link = Html::a('<i class="fa fa-print"></i>', ['/item/printer', 'machine_type_id' => MachineType::MACHINE_TYPE_PRINTER, 'id' => $model->id, 'ru' => $ru], [
                                'class' => 'modal-remote',
                                'title' => Yii::t('app', 'Printer'),
                                'data-toggle' => 'tooltip',
                            ]) . ' ';
                    }
                }
                $machines = [];
                foreach ($model->itemToMachines as $itemToMachine) {
                    $machines[] = Html::tag('strong', $itemToMachine->machine->name) . '<br>' . Yii::$app->formatter->asNtext(trim($itemToMachine->details));
                }
                $machine = '<hr style="margin: 2px 0;">' . $link . implode('<hr style="margin: 2px 0;">', $machines);
            }

            // artwork notes
            $artworkNotes = '';
            if (in_array('name.artwork_notes', $showColumns)) {
                $artworkNotes = $model->artwork_notes ? '<br>' . Html::tag('small', Yii::$app->formatter->asNtext($model->artwork_notes)) : '';
            }


            return $name . $dates . $links . $area . $perimeter . $size . $description . $machine . $artworkNotes;
        },
        'format' => 'raw',
    ];
}


if (in_array('options', $showColumns)) {
    $columns[] = [
        'label' => 'options',
        'value' => function ($model) use ($ru, $showColumns) {
            /** @var Item $model */
            $description = $model->getDescription([
                'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                'ignoreOptions' => [Option::OPTION_ARTWORK],
                'listOptions' => ['class' => 'list-unstyled'],
            ]);

            $icons = $model->getIcons();
            if (in_array('options.icons.print_tag', $showColumns)) {
                $icons .= $model->getPrintTagIcon() . ' ';
            }
            if ($icons) {
                $icons .= '<br>';
            }

            $area = $model->getArea();
            if ($area) {
                $area = Html::tag('span', ceil($area) . 'm<sup>2</sup>', ['class' => 'label label-default']) . ' ';
            } else {
                $area = '';
            }

            $perimeter = $model->getPerimeter();
            if ($perimeter) {
                $perimeter = Html::tag('span', ceil($perimeter) . 'm', ['class' => 'label label-default']) . ' ';
            } else {
                $perimeter = '';
            }

            $size = Html::tag('span', str_replace(' ', '&nbsp;', $model->getSizeHtml()), ['class' => 'label label-default']) . '<br>';

            $machine = '';
            if (in_array('options.machine', $showColumns)) {
                $link = '';
                if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                    if (Y::user()->can('app_item_printer', ['route' => true])) {
                        $link = Html::a('<i class="fa fa-print"></i>', ['/item/printer', 'machine_type_id' => MachineType::MACHINE_TYPE_PRINTER, 'id' => $model->id, 'ru' => $ru], [
                                'class' => 'modal-remote',
                                'title' => Yii::t('app', 'Printer'),
                                'data-toggle' => 'tooltip',
                            ]) . ' ';
                    }
                }
                $machines = [];
                foreach ($model->itemToMachines as $itemToMachine) {
                    $machines[] = Html::tag('strong', $itemToMachine->machine->name) . '<br>' . Yii::$app->formatter->asNtext(trim($itemToMachine->details));
                }
                $machine = $link . implode('<hr>', $machines);
            }

            $artworkNotes = '';
            if (in_array('options.artwork_notes', $showColumns)) {
                $artworkNotes = $model->artwork_notes ? '<br>' . Html::tag('small', Yii::$app->formatter->asNtext($model->artwork_notes)) : '';
            }

            return $icons . $area . $perimeter . $size . $description . $machine . $artworkNotes;
        },
        'contentOptions' => ['class' => 'text-right'],
        'format' => 'raw',
    ];
}

if (in_array('machine', $showColumns)) {
    $columns[] = [
        'label' => 'machine',
        'value' => function ($model) use ($ru) {
            /** @var Item $model */
            $link = '';
            if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                if (Y::user()->can('app_item_printer', ['route' => true])) {
                    $link = Html::a('<i class="fa fa-print"></i>', ['/item/printer', 'machine_type_id' => MachineType::MACHINE_TYPE_PRINTER, 'id' => $model->id, 'ru' => $ru], [
                            'class' => 'modal-remote',
                            'title' => Yii::t('app', 'Printer'),
                            'data-toggle' => 'tooltip',
                        ]) . ' ';
                }
            }
            $machines = [];
            foreach ($model->itemToMachines as $itemToMachine) {
                $machines[] = Html::tag('strong', $itemToMachine->machine->name) . '<br>' . Yii::$app->formatter->asNtext(trim($itemToMachine->details));
            }
            return $link . implode('<hr>', $machines);
        },
        'format' => 'raw',
    ];
}

$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';
$panelAfter = false;
$actionButtons = false;
if (in_array('progress_unit', $showColumns) && $progressUnitItemType && $progressUnitStatus) {
    $actionButtons = implode(' ', [
        Html::a(Yii::t('app', 'Progress Units'), ['/unit/progress', 'status' => $progressUnitStatus, 'ru' => $ru], [
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
        Html::a(Yii::t('app', 'Progress Items'), ['/item/progress', 'status' => $progressItemStatus, 'ru' => $ru], [
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


echo SortableGridView::widget([
    'id' => $grid_id,
    'orderUrl' => ['/sort-order/sort', 'model' => $sortModel],
    'sortModel' => $sortModel,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'tableOptions' => [
        'class' => 'no-margin',
    ],
    'striped' => false,
    'condensed' => true,
    'bordered' => false,
    'showHeader' => false,
    'panel' => $title ? [
        'heading' => $title,
        'footer' => false,
        'after' => $panelAfter,
        'before' => false,
        'type' => GridView::TYPE_DEFAULT,
    ] : false,
    'panelHeadingTemplate' => ($panelHeadingExtra ? '<div class="pull-right">' . $panelHeadingExtra . '</div>' : '') . '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
]);
