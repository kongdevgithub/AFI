<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Item;
use app\models\ItemType;
use app\models\search\ItemSearch;
use app\models\search\UnitSearch;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Fabrication');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

$showColumns = [
    'expand_items',
    'name',
    'name.name',
    'name.name.company',
    'name.links',
    'name.dates',
    'progress_item',
    'progress_item.perimeter',
    'progress_item.icons',
    'progress_unit',
    'progress_unit.perimeter',
    'progress_unit.icons',
];
$expandItemsShowColumns = [
    'status',
    'status.checkbox',
    'status.icons',
    'name',
    'name.name',
    'name.description',
    'name.area',
    'name.perimeter',
    'name.size',
    'name.links',
    'name.dates',
];
?>

<div class="dashboard-fabrication">

    <div class="row">

        <div class="col-sm-4">

            <?php
            // Awaiting Info
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-fabrication/awaitingInfo',
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
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Awaiting Info'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Awaiting Info'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressItemStatus' => 'item-fabrication/awaitingInfo',
                'progressItemItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'awaitingInfo',
            ]);
            ?>

            <?php
            // Pre Fabrication
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/preFabrication',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];

            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Pre Fabrication'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Pre Fabrication'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/preFabrication',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'preFabrication',
            ]);
            ?>

        </div>
        <div class="col-sm-4">

            <?php
            // Powdercoat
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/powdercoat',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Powdercoat'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Powdercoat'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/powdercoat',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'powdercoat',
            ]);
            ?>

            <?php
            // Machining
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/manufacture',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Machining'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Machining'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/manufacture',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'manufacture',

            ]);
            ?>

            <?php
            // Fabrication
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/fabrication',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Fabrication'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Fabrication'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/fabrication',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'fabrication',

            ]);
            ?>

            <?php
            // Print Fabrication
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/fabrication',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Print Fabrication'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Print Fabrication'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-print/fabrication',
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsUnitStatus' => 'print',
            ]);
            ?>

        </div>
        <div class="col-sm-4">

            <?php
            // Cut
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/cut',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Cut'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Cut'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/cut',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'cut',
            ]);
            ?>

            <?php
            // Light
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/light',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Light'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Light'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/light',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'light',
            ]);
            ?>


            <?php
            // Quality
            $jobs = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-fabrication/production',
                'status' => 'unit-fabrication/quality',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($jobs[$unit->item->product->job_id])) {
                    $jobs[$unit->item->product->job_id] = $unit->item->product->job_id;
                }
            }
            $params = ['JobSearch' => [
                'id' => $jobs ?: 'fake',
            ]];
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => $showColumns,
                'expandItemsShowColumns' => $expandItemsShowColumns,
                'title' => Html::a(Yii::t('app', 'Quality'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Quality'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'progressUnitStatus' => 'unit-fabrication/quality',
                'progressUnitItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_FABRICATION,
                'expandItemsUnitStatus' => 'quality',
            ]);
            ?>

            <?php
            // prebuild required
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'product__prebuild_required' => 1,
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
            echo $this->render('_jobs', [
                'pageSize' => 100,
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Prebuild Required'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-print/approval',
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
            ]);
            ?>

        </div>

    </div>

</div>