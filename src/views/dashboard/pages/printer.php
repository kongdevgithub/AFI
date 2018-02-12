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

$this->title = Yii::t('app', 'Printer');

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

<div class="dashboard-printer-2">

    <div class="row">

        <div class="col-sm-4">

            <?php
            // unassigned
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
            if ($items) {
                $params = ['ItemSearch' => [
                    'id' => $items ?: 'fake',
                    'machine' => false,
                ]];
                echo $this->render('_items', [
                    'showColumns' => $showColumns,
                    'title' => Html::a(Yii::t('app', 'Unassigned'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                    'params' => $params,
                    'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                    'progressUnitStatus' => 'unit-print/printing',
                    'includeUnitStatus' => 'printing',
                    //'sortModel' => 'Dashboard_Printer_Unassigned',
                ]);
            }
            ?>

        </div>
        <div class="col-sm-4">

            <?php
            // redo
            $items = [];
            $params = ['UnitSearch' => [
                'job__status' => 'job/production',
                'product__status' => 'product/production',
                'item__status' => 'item-print/production',
                'status' => 'unit-print/redo',
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
                'title' => Html::a(Yii::t('app', 'Redo'), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/redo',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_Redo',
            ]);
            ?>

        </div>
        <div class="col-sm-4">

            <?php
            $currentPrinter = Machine::find()->where(['id' => Machine::MACHINE_SEVEN])->one();
            // Printer 7
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
                'machine' => $currentPrinter->id,
            ]];
            echo $this->render('_items', [
                'showColumns' => $showColumns,
                'title' => Html::a(Yii::t('app', $currentPrinter->name), ['/item/index', 'ItemSearch' => $params['ItemSearch']]),
                'params' => $params,
                'progressUnitItemType' => ItemType::ITEM_TYPE_PRINT,
                'progressUnitStatus' => 'unit-print/printing',
                'includeUnitStatus' => 'printing',
                //'sortModel' => 'Dashboard_Printer_Printer7',
            ]);
            ?>

        </div>


    </div>

</div>