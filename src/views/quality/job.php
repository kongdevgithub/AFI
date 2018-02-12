<?php

use app\models\Job;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var Job $job
 */

$this->title = implode(' | ', [$job->name, $job->company->name]);

$this->params['breadcrumb-home'] = ['index'];
$this->params['breadcrumbs'][] = 'job-' . $job->vid;


$checkedProducts = $uncheckedProducts = [];

foreach ($job->products as $product) {
    if ($product->status != 'product/production') {
        continue;
    }
    $checkedLinks = $uncheckedLinks = [];
    foreach ($product->items as $item) {
        if ($item->status != 'item-fabrication/production') {
            continue;
        }
        $totalCount = $item->getMaterialCheckTotalCount();
        if (!$totalCount) {
            continue;
        }
        $checkCount = $item->getMaterialCheckedCount();

        $itemQuantity = $item->quantity * $item->product->quantity;
        $itemName = $item->name;
        if ($item->getSizeHtml()) {
            $itemName .= ' | ' . $item->getSizeHtml();
        }
        $itemName .= '<br>' . Html::tag('small', ' item-' . $item->id);
        $countString = $checkCount . '/' . $totalCount . ' x' . $itemQuantity;
        if ($checkCount == $totalCount) {
            $checkedLinks[] = Html::a(implode('', [
                Html::tag('span', $countString, ['class' => 'badge label-success']),
                $itemName,
            ]), ['item', 'id' => $item->id], ['class' => 'list-group-item']);
        } else {
            $uncheckedLinks[] = Html::a(implode('', [
                Html::tag('span', $countString, ['class' => 'badge label-' . ($checkCount ? 'warning' : 'danger')]),
                $itemName,
            ]), ['item', 'id' => $item->id], ['class' => 'list-group-item']);
        }
    }
    if ($checkedLinks || $uncheckedLinks) {
        $productLinks = Html::tag('h4', $product->name . '<br>' . Html::tag('small', ' product-' . $product->id));
        if ($uncheckedLinks) {
            $productLinks .= Html::tag('div', implode('', $uncheckedLinks), ['class' => 'list-group']);
        }
        if ($checkedLinks) {
            $productLinks .= Html::tag('div', implode('', $checkedLinks), ['class' => 'list-group']);
        }

        if ($uncheckedLinks) {
            $uncheckedProducts[] = $productLinks;
        } else {
            $checkedProducts[] = $productLinks;
        }
    }
}

if ($uncheckedProducts) {
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'To Check') ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($uncheckedProducts as $product) {
                echo $product;
            }
            ?>
        </div>
    </div>
    <?php
}
if ($checkedProducts) {
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Checked') ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($checkedProducts as $product) {
                echo $product;
            }
            ?>
        </div>
    </div>
    <?php
}
