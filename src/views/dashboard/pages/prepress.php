<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Item;
use app\models\ItemType;
use app\models\search\ItemSearch;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Prepress');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();
?>

<div class="dashboard-prepress">

    <div class="row">

        <div class="col-sm-4">

            <?php
            // change request
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-print/change',
                'quantity' => '>0',
            ]];
            echo $this->render('_items', [
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.job_name',
                    'name.job_details',
                    'name.links',
                    'name.job_links',
                    'name.dates',
                    'name.job_dates',
                    'details',
                    'options',
                    'artwork',
                    'status',
                    'status.checkbox',
                    'progress_item',
                ],
                'title' => Html::a(Yii::t('app', 'Change Request'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressItemStatus' => 'item-print/change',
            ]);
            ?>

            <?php
            // em change request
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/change',
                'quantity' => '>0',
            ]];
            echo $this->render('_items', [
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.job_name',
                    'name.job_details',
                    'name.links',
                    'name.job_links',
                    'name.dates',
                    'name.job_dates',
                    'details',
                    'options',
                    'artwork',
                    'status',
                    'status.checkbox',
                    'progress_item',
                ],
                'title' => Html::a(Yii::t('app', 'EM Change Request'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'progressItemStatus' => 'item-emPrint/change',
            ]);
            ?>

            <?php
            // design
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-print/design',
                'quantity' => '>0',
            ]];
            echo $this->render('_items', [
                'showColumns' => [
                    'details',
                    'options',
                    'artwork',
                    'status',
                    'progress_item',
                ],
                'title' => Html::a(Yii::t('app', 'Design'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressItemStatus' => 'item-print/design',
            ]);
            ?>

            <?php
            // em design
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-emPrint/design',
                'quantity' => '>0',
            ]];
            echo $this->render('_items', [
                'showColumns' => [
                    'details',
                    'options',
                    'artwork',
                    'status',
                    'progress_item',
                ],
                'title' => Html::a(Yii::t('app', 'EM Design'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'progressItemStatus' => 'item-emPrint/design',
            ]);
            ?>

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
            echo $this->render('_jobs', [
                'params' => $params,
                'showColumns' => ['name', 'name.name', 'name.name.company', 'name.details', 'name.links', 'name.dates', 'expand_items', 'progress_item', 'progress_item.icons'],
                'expandItemsShowColumns' => [
                    'status',
                    'status.artwork',
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
                ],
                'title' => Html::a(Yii::t('app', 'EM Print To Order'), ['/item/index', 'JobSearch' => $params['JobSearch']]),
                'progressItemStatus' => 'item-emPrint/order',
                'progressItemItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_EM_PRINT,
            ]);
            ?>

        </div>

        <div class="col-sm-4">
            <?php
            // send approval
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => [
                    'item-print/artwork',
                    'item-emPrint/artwork'
                ],
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
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.details',
                    'name.links',
                    'name.dates',
                    'expand_items',
                    'approval',
                ],
                'expandItemsShowColumns' => [
                    'status',
                    'status.artwork',
                    'status.checkbox',
                    'status.icons',
                    'status.icons.print_tag',
                    'name',
                    'name.name',
                    'name.description',
                    'name.area',
                    'name.perimeter',
                    'name.size',
                    'name.links',
                    'name.dates',
                ],
                'title' => Html::a(Yii::t('app', 'Send Approval'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'expandItemsItemType' => [
                    ItemType::ITEM_TYPE_PRINT,
                    ItemType::ITEM_TYPE_EM_PRINT,
                ],
            ]);
            ?>

            <?php
            // out on approval
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => [
                    'item-print/approval',
                    'item-emPrint/approval',
                ],
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
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.details',
                    'name.links',
                    'name.dates',
                    'expand_items',
                ],
                'expandItemsShowColumns' => [
                    'status',
                    'status.artwork',
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
                ],
                'title' => Html::a(Yii::t('app', 'Out on Approval'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'expandItemsItemType' => [
                    ItemType::ITEM_TYPE_PRINT,
                    ItemType::ITEM_TYPE_EM_PRINT
                ],
            ]);
            ?>

        </div>

        <div class="col-sm-4">

            <?php
            // rip
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'status' => 'item-print/rip',
                'quantity' => '>0',
            ]];
            echo $this->render('_items', [
                'showColumns' => [
                    'name',
                    'name.name',
                    'name.job_name',
                    'name.job_details',
                    'name.links',
                    'name.links.job',
                    'name.dates',
                    'name.dates.job',
                    'details',
                    'options',
                    'options.machine',
                    'status',
                    'status.checkbox',
                    'status.artwork',
                    'progress_item',
                ],
                'title' => Html::a(Yii::t('app', 'Rip'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressItemStatus' => 'item-print/rip',
            ]);
            ?>

        </div>

    </div>

</div>