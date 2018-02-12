<?php
/**
 * @var View $this
 * @var Product[] $products
 */

use app\models\Product;
use yii\web\View;
use yii\widgets\DetailView;

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Yii::t('app', 'Product Margins'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $total = 0;
        $attributes = [];
        foreach ($products as $product) {
            if ($product->quote_quantity <= 0) {
                continue;
            }
            if ($product->job->hideTotals()) {
                continue;
            }
            $factor = $product->job->status == 'job/quote' ? ($product->job->quote_win_chance / 100) : 1;
            $value = (1 - $product->quote_factor) * $product->quote_total_price * $product->job->quote_markup * -1;
            $total += $value * $factor;
            //$attributes[] = [
            //    'label' => $product->name,
            //    'value' => number_format($value, 2) . ' <span class="label label-default">' . ($product->quote_factor * 1) . '</span>',
            //    'format' => 'raw',
            //];
        }
        $attributes[] = [
            'label' => Yii::t('app', 'Total  Offset'),
            'value' => number_format($total, 2),
            'format' => 'raw',
        ];
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>

    </div>
</div>

