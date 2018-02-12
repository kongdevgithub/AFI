<?php

use app\models\Item;
use app\models\Job;
use app\models\search\UnitSearch;
use yii\helpers\Html;

$jobs = [];
$params = ['UnitSearch' => [
    'job__status' => 'job/production',
    'product__status' => 'product/production',
    'item__status' => 'item-fabrication/production',
    'status' => $status,
    'quantity' => '>0',
]];
$unitSearch = new UnitSearch;
$dataProvider = $unitSearch->search($params);
$dataProvider->pagination->pageSize = 1000;
foreach ($dataProvider->getModels() as $unit) {
    /** @var Item $item */
    if (!isset($jobs[$unit->item->product->job_id][$unit->item_id])) {
        $jobs[$unit->item->product->job_id][$unit->item_id] = Item::findOne($unit->item_id);;
    }
}

$checkedLinks = $uncheckedLinks = [];
foreach ($jobs as $job_id => $items) {
    $totalItemsCount = $checkedItemsCount = 0;
    foreach ($items as $item) {
        if (!$item->getMaterialCheckTotalCount()) {
            continue;
        }
        $totalItemsCount++;
        if ($item->isMaterialChecked()) {
            $checkedItemsCount++;
        }
    }
    if (!$totalItemsCount) {
        continue;
    }
    $job = Job::findOne($job_id);
    if ($totalItemsCount == $checkedItemsCount) {
        $checkedLinks[] = Html::a(implode('', [
            Html::tag('span', $checkedItemsCount . '/' . $totalItemsCount, ['class' => 'badge label-success']),
            $job->getTitle(),
        ]), [$action, 'id' => $job->id], ['class' => 'list-group-item']);
    } else {
        $uncheckedLinks[] = Html::a(implode('', [
            Html::tag('span', $checkedItemsCount . '/' . $totalItemsCount, ['class' => 'badge label-' . ($checkedItemsCount ? 'warning' : 'danger')]),
            $job->getTitle(),
        ]), [$action, 'id' => $job->id], ['class' => 'list-group-item']);
    }
}

if ($checkedLinks || $uncheckedLinks) {
    echo Html::tag('h3', $title);
    if ($uncheckedLinks) {
        echo Html::tag('div', implode('', $uncheckedLinks), ['class' => 'list-group']);
    }
    if ($checkedLinks) {
        echo Html::tag('div', implode('', $checkedLinks), ['class' => 'list-group']);
    }
}


