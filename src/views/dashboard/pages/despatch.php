<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Item;
use app\models\ItemType;
use app\models\search\ItemSearch;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Despatch');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

?>

<div class="dashboard-despatch">

    <div class="row">
        <div class="col-md-4">

            <?php
            // No Shipping Address
            $params = [
                'JobSearch' => [
                    'status' => 'job/production',
                    'shippingAddress' => false,
                ],
            ];
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
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_unit', 'progress_item.icons'],
                'expandItemsShowColumns' => ['name', 'name.name', 'name.links', 'name.dates', 'options', 'status', 'status.checkbox'],
                'title' => Html::a(Yii::t('app', 'EM Print In Progress'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressUnitStatus' => 'unit-emPrint/outsource',
                'progressUnitItemType' => ItemType::ITEM_TYPE_EM_PRINT,
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

        </div>
        <div class="col-md-4">

            <?php
            // Freight Quote
            $params = [
                'JobSearch' => [
                    'freight_quote_requested' => true,
                ],
            ];
            echo $this->render('_jobs', [
                'showColumns' => ['name', 'name.name', 'name.details', 'name.links', 'name.dates', 'status'],
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'Freight Quote'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);

            ?>

            <?php
            // Stock Check
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-hardware/stockCheck',
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
                'title' => Html::a(Yii::t('app', 'Stock Check'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'orderBy' => ['job.prebuild_date' => SORT_ASC, 'job.id' => SORT_ASC],
                'progressItemStatus' => 'item-hardware/stockCheck',
                'progressItemItemType' => ItemType::ITEM_TYPE_HARDWARE,
                'expandItemsItemType' => ItemType::ITEM_TYPE_HARDWARE,
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // Pickups Awaiting Request
            $params = ['PickupSearch' => ['status' => 'pickup/complete']];
            ?>
            <?= $this->render('_pickups', [
                'showColumns' => ['id', 'jobs', 'status', 'status.checkbox'],
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'Pickups Awaiting Request'), ['/pickup/index', 'PickupSearch' => $params['PickupSearch']]),
                'params' => $params,
            ]) ?>

        </div>
    </div>

</div>