<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Item;
use app\models\ItemType;
use app\models\search\ItemSearch;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Coordination');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

$searchParams = [];

$staffs = [];
foreach (User::find()->byRole('csr')->all() as $_staff) {
    $staffs[$_staff->id] = $_staff;
}
foreach (User::find()->byRole('rep')->all() as $_staff) {
    $staffs[$_staff->id] = $_staff;
}
$staff_id = Y::GET('staff_id') ?: Yii::$app->user->id;
if (!$staff_id || !in_array($staff_id, ArrayHelper::map($staffs, 'id', 'id'))) {
    $staff_id = 'all';
}
$staff = $staff_id != 'all' ? User::findOne($staff_id) : false;
if ($staff_id != 'all') {
    $searchParams['JobSearch']['staff_id'] = $staff_id;
}
?>

<div class="dashboard-coordination">

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
            // staff
            if (Yii::$app->user->can('manager')) {
                echo '<h4>Staff</h4>';
                echo $this->render('/report/pages/_filter-staff', [
                    'staff' => $staff,
                    'staffUrlParam' => 'staff_id',
                    'role' => ['csr', 'rep'],
                    'url' => ['/dashboard/index', 'dashboard' => 'coordination'],
                ]);
            }
            ?>
        </div>
    </div>

    <div class="row">

        <div class="col-sm-4">

            <?php
            // Production Pending
            $params = [
                'JobSearch' => [
                    'status' => 'job/productionPending',
                ],
            ];
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => [
                    'status',
                    'name',
                    'name.name',
                    'name.name.company',
                    'name.details',
                    'name.links',
                    'name.dates',
                ],
                'title' => Html::a(Yii::t('app', 'Production Pending'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
            ]);
            ?>

            <?php
            // Production Draft
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/draft',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => [
                    'status',
                    'name',
                    'name.name',
                    'name.name.company',
                    'name.details',
                    'name.links',
                    'name.dates',
                ],
                'title' => Html::a(Yii::t('app', 'Production Draft'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
            ]);
            ?>

            <?php
            // No Shipping Address
            $params = ['JobSearch' => ['status' => 'job/production', 'shippingAddress' => false]];
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'showColumns' => ['name', 'name.name', 'name.details', 'name.links', 'name.dates', 'status'],
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'No Shipping Address'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>

            <?php
            // print awaiting info
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-print/awaitingInfo',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.name.company',
                    'name.details',
                    'name.links',
                    'name.dates',
                    'expand_items',
                    'progress_item',
                    'progress_item.icons',
                ],
                'expandItemsShowColumns' => [
                    'name',
                    'name.name',
                    'name.links',
                    'name.dates',
                    'options',
                    'status',
                    'status.checkbox',
                ],
                'title' => Html::a(Yii::t('app', 'Print Awaiting Info'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-print/awaitingInfo',
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
            ]);
            ?>

            <?php
            // em print awaiting info
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/awaitingInfo',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.name.company',
                    'name.details',
                    'name.links',
                    'name.dates',
                    'expand_items',
                    'progress_item',
                    'progress_item.icons',
                ],
                'expandItemsShowColumns' => [
                    'name',
                    'name.name',
                    'name.links',
                    'name.dates',
                    'options',
                    'status',
                    'status.checkbox',
                ],
                'title' => Html::a(Yii::t('app', 'EM Print Awaiting Info'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emPrint/awaitingInfo',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_PRINT,
            ]);
            ?>

            <?php
            // em hardware awaiting info
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emHardware/awaitingInfo',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.name.company',
                    'name.details',
                    'name.links',
                    'name.dates',
                    'expand_items',
                    'progress_item',
                    'progress_item.icons',
                ],
                'expandItemsShowColumns' => [
                    'name',
                    'name.name',
                    'name.links',
                    'name.dates',
                    'options',
                    'status',
                    'status.checkbox',
                ],
                'title' => Html::a(Yii::t('app', 'EM Hardware Awaiting Info'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emHardware/awaitingInfo',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
            ]);
            ?>

        </div>
        <div class="col-sm-4">

            <?php
            // em print to order
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/order',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Print To Order'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emPrint/order',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_PRINT,
            ]);
            ?>

            <?php
            // em hardware to order
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emHardware/order',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Hardware To Order'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emHardware/order',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
            ]);
            ?>

            <?php
            // installations to order
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-installation/order',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Installations To Order'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-installation/order',
                'progressItemItemType' => ItemType::ITEM_TYPE_INSTALLATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_INSTALLATION,
            ]);
            ?>


            <?php
            // em print in progress
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/production',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Print In Progress'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emPrint/production',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_PRINT,
            ]);
            ?>

            <?php
            // em hardware in progress
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emHardware/production',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Hardware In Progress'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emHardware/production',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_HARDWARE,
            ]);
            ?>

            <?php
            //// installations in progress
            //$params = ['ItemSearch' => [
            //    'job__status' => 'job/production',
            //    'product__status' => 'product/production',
            //    'status' => 'item-installation/production',
            //    'quantity' => '>0',
            //]];
            //$itemSearch = new ItemSearch;
            //$dataProvider = $itemSearch->search($params);
            //$dataProvider->pagination->pageSize = 1000;
            //$jobs = [];
            //foreach ($dataProvider->getModels() as $item) {
            //    /** @var Item $item */
            //    if (!isset($jobs[$item->product->job_id])) {
            //        $jobs[$item->product->job_id] = $item->product->job_id;
            //    }
            //}
            //$params = [
            //    'JobSearch' => [
            //        'id' => $jobs ?: 'fake',
            //    ],
            //];
            //if ($staff) {
            //    $params['JobSearch']['staff_id'] = $staff->id;
            //}
            //echo $this->render('_jobs', [
            //    'params' => $params,
            //    'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
            //    'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
            //    'title' => Html::a(Yii::t('app', 'Installations In Progress'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
            //    'progressItemStatus' => 'item-installation/production',
            //    'progressItemItemType' => ItemType::ITEM_TYPE_INSTALLATION,
            //    'expandItemsItemType' => ItemType::ITEM_TYPE_INSTALLATION,
            //]);
            ?>

            <?php
            // Upcoming Installations
            $params = ['ItemSearch' => [
                'job__status' => ['job/production', 'job/despatch', 'job/packed'],
                'product__status' => 'product/production',
                'status' => 'item-installation/production',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Upcoming Installations'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Upcoming Installations'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'orderBy' => ['job.prebuild_date' => SORT_ASC, 'job.id' => SORT_ASC],
                'progressItemStatus' => 'item-installation/production',
                'progressItemItemType' => ItemType::ITEM_TYPE_INSTALLATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_INSTALLATION,
            ]);
            ?>

            <?php
            // Current Installations
            $params = ['ItemSearch' => [
                'job__status' => 'job/complete',
                'product__status' => 'product/production',
                'status' => 'item-installation/production',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'pageSize' => 1000,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Current Installations'), ['/job/index', 'ItemSearch' => $params['JobSearch']]),
                'headerCallback' => function ($dataProvider) use ($params) {
                    return Html::a('<span class="fa fa-print"></span>', [
                        '/dashboard/print',
                        'view' => '_jobs',
                        'heading' => Yii::t('app', 'Current Installations'),
                        'params' => [
                            'showColumns' => ['name', 'name.name', 'name.name.staff', 'name.name.company', 'name.links', 'name.dates'],
                            'params' => $params,
                        ],
                    ], ['target' => '_blank']);
                },
                'params' => $params,
                'orderBy' => ['job.prebuild_date' => SORT_ASC, 'job.id' => SORT_ASC],
                'progressItemStatus' => 'item-installation/production',
                'progressItemItemType' => ItemType::ITEM_TYPE_INSTALLATION,
                'expandItemsItemType' => ItemType::ITEM_TYPE_INSTALLATION,
            ]);
            ?>

        </div>
        <div class="col-sm-4">

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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Prebuild Required'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-print/approval',
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
            ]);
            ?>


            <?php
            // out on approval
            $params = ['ItemSearch' => [
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'Out on Approval'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-print/approval',
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
            ]);
            ?>

            <?php
            // em out on approval
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/approval',
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
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Out on Approval'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emPrint/approval',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_PRINT,
            ]);
            ?>

            <?php
            // On Hold and Suspended
            $params = ['JobSearch' => ['status' => ['job/hold', 'job/suspended']]];
            if ($staff) {
                $params['JobSearch']['staff_id'] = $staff->id;
            }
            echo $this->render('_jobs', [
                'showColumns' => ['name', 'name.name', 'name.details', 'name.links', 'name.dates', 'status'],
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'On Hold and Suspended'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>

        </div>

    </div>

</div>