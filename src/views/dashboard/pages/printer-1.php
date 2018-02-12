<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Item;
use app\models\ItemType;
use app\models\Machine;
use app\models\search\UnitSearch;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Printer 1');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

$showColumns = [
    'status',
    'status.checkbox',
    'status.artwork',
    'status.icons',
    'status.icons.print_tag',
    'name',
    'name.name',
    'name.job_name',
    'name.job_details',
    'name.description',
    'name.area',
    'name.perimeter',
    'name.size',
    'name.machine',
    'name.links',
    'name.links.job',
    'name.dates',
    'name.dates.job',
    'progress_unit',
];

?>

<div class="dashboard-printer">

    <div class="row">

        <div class="col-sm-3">

            <?php
            // Mtex
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/printing',
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
                'machine' => Machine::MACHINE_MTEX,
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Mtex'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/printing',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_Mtex',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Mtex HS
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/printing',
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
                'machine' => Machine::MACHINE_MTEX_HS,
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Mtex HS'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/printing',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_MtexHS',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Mtex HS 2
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/printing',
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
                'machine' => Machine::MACHINE_MTEX_HS_2,
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Mtex HS 2'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/printing',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_MtexHS2',
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Mtex HS 3
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/printing',
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
                'machine' => Machine::MACHINE_MTEX_HS_3,
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', 'Mtex HS 3'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/printing',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_MtexHS3',
            ]);
            ?>

        </div>

    </div>

</div>