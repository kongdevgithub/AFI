<?php

use app\components\NfcTools;
use app\models\Job;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var Job $job
 */

$this->title = implode(' | ', [$item->product->job->name, $item->product->job->company->name]);

$this->params['breadcrumb-home'] = ['index'];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $item->product->job->vid, 'url' => ['job', 'id' => $item->product->job->id]];
//$this->params['breadcrumbs'][] = ['label' => 'product-' . $item->product->id, 'url' => ['product', 'id' => $item->product->id]];
$this->params['breadcrumbs'][] = 'item-' . $item->id;


//echo NfcTools::scanButton();
//debug($_GET);


//foreach ($job->products as $product) {
//    if ($product->status != 'product/production') {
//        continue;
//    }
//    $links = [];
//    foreach ($product->items as $item) {
//        if ($item->status != 'item-fabrication/production') {
//            continue;
//        }
//        $links[] = Html::a('i' . $item->id . ': ' . $item->name, ['nfc/item', 'id' => $job->id], ['class' => 'list-group-item']);
//    }
//    if (!$links) {
//        continue;
//    }
//    echo Html::tag('h3', 'p' . $product->id . ': ' . $product->name);
//    echo Html::tag('div', implode('', $links), ['class' => 'list-group']);
//}
//


$itemQuantity = $item->quantity * $item->product->quantity;
$checkLinks = $uncheckLinks = [];
foreach ($item->getMaterials() as $material) {
    if (!$material['quality_check']) continue;
    $checkedQuantity = $material['checked_quantity'];
    $requiredQuantity = $material['quantity'] * $itemQuantity;
    $visualQuantity = $itemQuantity != 1 ? $requiredQuantity . ' (' . $material['quantity'] . $material['unit_of_measure'] . ' x ' . $itemQuantity . ')' : $requiredQuantity . $material['unit_of_measure'];
    $componentName = 'c' . $material['component_id'] . ': ' . $material['code'] . ' - ' . $material['name'];
    $url = ['check', 'id' => $material['id'], 'quantity' => $requiredQuantity];
    $confirm = false;
    if (abs($checkedQuantity == $requiredQuantity) < 0.000001) {
        if ($material['quality_code']
            //&& !Yii::$app->user->can('admin') // TODO
        ) {
            $url = NfcTools::scanUrl($url);
        } else {
            $confirm = 'Check for' . "\n" . $visualQuantity . "\n" . $material['code'] . "\n" . $material['name'];
        }
        $checkLinks[] = Html::a(Html::tag('span', $visualQuantity, ['class' => 'badge label-danger']) . $componentName, $url, ['class' => 'list-group-item', 'data-confirm' => $confirm]);
    } else {
        $url['quantity'] = 0;
        $confirm = 'Undo for' . "\n" . $visualQuantity . "\n" . $material['code'] . "\n" . $material['name'];
        $uncheckLinks[] = Html::a(Html::tag('span', $visualQuantity, ['class' => 'badge label-success']) . $componentName, $url, ['class' => 'list-group-item', 'data-confirm' => $confirm]);
    }
}
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?php
            $itemName = [$item->name, $item->product->name];
            if ($item->getSizeHtml()) {
                $itemName[] = $item->getSizeHtml();
            }
            echo implode(' | ', $itemName);
            ?></h3>
    </div>
    <div class="box-body">
        <?php
        if ($checkLinks) {
            echo Html::tag('h3', 'To Check');
            echo Html::tag('div', implode('', $checkLinks), ['class' => 'list-group']);
        }
        if ($uncheckLinks) {
            echo Html::tag('h3', 'Checked');
            echo Html::tag('div', implode('', $uncheckLinks), ['class' => 'list-group']);
        }
        ?>
    </div>
</div>
