<?php

use app\models\AccountTerm;
use cornernote\shortcuts\Y;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Status'); ?></h3>
        <div class="box-tools pull-right text-right">
            <?= $model->getIcons() ?>
        </div>
    </div>
    <div class="box-body">
        <?= $model->getStatusButtons(true) ?>
        <hr>
        <?php

        $productCount = $model->getProducts()->count();
        $itemCount = 0;
        $unitCount = 0;
        foreach ($model->products as $product) {
            $itemCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->count();
            $unitCount += $product->getItems()->andWhere(['>', 'item.quantity', '0'])->sum('item.quantity') * $product->quantity;
        }
        $attributes = [];
        $attributes[] = [
            'label' => Yii::t('app', 'Product Count'),
            'format' => 'raw',
            'value' => number_format($productCount, 0),
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Item Count'),
            'format' => 'raw',
            'value' => number_format($itemCount, 0),
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Unit Count'),
            'format' => 'raw',
            'value' => number_format($unitCount, 0),
        ];
        $area = $model->getArea();
        if ($area) {
            $attributes[] = [
                'label' => Yii::t('app', 'Print Size'),
                'format' => 'raw',
                'value' => number_format($model->getArea(), 2) . ' m<sup>2</sup>',
            ];
        }
        $perimeter = $model->getPerimeter();
        if ($perimeter) {
            $attributes[] = [
                'label' => Yii::t('app', 'Fabrication Size'),
                'format' => 'raw',
                'value' => number_format($perimeter, 2) . ' m',
            ];
        }
        echo DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>

