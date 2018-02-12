<?php
/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

use app\models\Item;

$item_type_id = isset($item_type_id) ? (is_array($item_type_id) ? $item_type_id : [$item_type_id]) : [];
$showColumns = isset($showColumns) ? $showColumns : ['name', 'name.name'];
$includeUnitStatus = isset($includeUnitStatus) ? $includeUnitStatus : null;

/** @var Item[] $items */
$items = [];
foreach ($model->products as $product) {
    foreach ($product->items as $item) {
        if (!$item->quantity) {
            continue;
        }
        if ($item_type_id && !in_array($item->item_type_id, $item_type_id)) {
            continue;
        }
        $items[] = $item->id;
    }
}

?>

<div class="kv-detail-content">

    <?php
    echo $this->render('_items', [
        'showColumns' => $showColumns,
        'includeUnitStatus' => $includeUnitStatus,
        'params' => ['ItemSearch' => [
            'id' => $items ?: 'fake',
        ]],
    ]);
    ?>

</div>