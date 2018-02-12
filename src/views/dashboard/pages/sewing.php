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

$this->title = Yii::t('app', 'Sewing');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

$showColumns = [
    'status',
    'status.checkbox',
    'status.artwork',
    'status.icons',
    'name',
    'name.name',
    'name.job_name',
    'name.job_details',
    'name.description',
    'name.area',
    'name.perimeter',
    'name.size',
    'name.links',
    'name.links.job',
    'name.dates',
    'name.dates.job',
    'progress_unit',
];
?>

<div class="dashboard-sewing">

    <div class="row">

        <div class="col-sm-3">

            <?php
            // Cutting
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/cutting',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($items[$unit->item_id])) {
                    $items[$unit->item_id] = $unit->item_id;
                }
            }
            $params = ['ItemSearch' => [
                'id' => $items ?: 'fake',
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Cutting'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/cutting',
                'includeUnitStatus' => 'cutting',
                //'sortModel' => 'Dashboard_Sewing_Cutting',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Sew Pending
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/sewPending',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($items[$unit->item_id])) {
                    $items[$unit->item_id] = $unit->item_id;
                }
            }
            $params = ['ItemSearch' => [
                'id' => $items ?: 'fake',
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Sew Pending'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/sewPending',
                'includeUnitStatus' => 'sewPending',
                //'sortModel' => 'Dashboard_Sewing_SewPending',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Sewing
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/sewing',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($items[$unit->item_id])) {
                    $items[$unit->item_id] = $unit->item_id;
                }
            }
            $params = ['ItemSearch' => [
                'id' => $items ?: 'fake',
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Sewing'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/sewing',
                'includeUnitStatus' => 'sewing',
                //'sortModel' => 'Dashboard_Sewing_Sewing',
            ]);
            ?>

            <?php
            // em Sewing
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-emPrint/production',
                'status' => 'unit-emPrint/sewing',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($items[$unit->item_id])) {
                    $items[$unit->item_id] = $unit->item_id;
                }
            }
            $params = ['ItemSearch' => [
                'id' => $items ?: 'fake',
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'EM Sewing'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_EM_PRINT,
                'progressUnitStatus' => 'unit-emPrint/sewing',
                'includeUnitStatus' => 'sewing',
                //'sortModel' => 'Dashboard_Sewing_EMSewing',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Quality
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/quality',
                'quantity' => '>0',
            ]];
            $unitSearch = new UnitSearch;
            $dataProvider = $unitSearch->search($params);
            $dataProvider->pagination->pageSize = 1000;
            foreach ($dataProvider->getModels() as $unit) {
                /** @var Item $item */
                if (!isset($items[$unit->item_id])) {
                    $items[$unit->item_id] = $unit->item_id;
                }
            }
            $params = ['ItemSearch' => [
                'id' => $items ?: 'fake',
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Quality'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/quality',
                'includeUnitStatus' => 'quality',
                //'sortModel' => 'Dashboard_Sewing_Quality',
            ]);
            ?>

            <?php
            // prebuild required
            $params = ['ItemSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'product__prebuild_required' => 1,
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
                'expandItemsShowColumns' => [
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
                ],
                'title' => Html::a(Yii::t('app', 'Prebuild Required'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'orderBy' => ['job.prebuild_date' => SORT_ASC, 'job.id' => SORT_ASC],
                'progressItemStatus' => 'item-print/approval',
                'progressItemItemType' => ItemType::ITEM_TYPE_PRINT,
                'expandItemsItemType' => ItemType::ITEM_TYPE_PRINT,
            ]);
            ?>

        </div>

    </div>

</div>