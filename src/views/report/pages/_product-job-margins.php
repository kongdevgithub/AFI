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
        <h3 class="panel-title"><?= Yii::t('app', 'Job Margins'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $cost = 0;
        $sell = 0;
        foreach ($products as $product) {
            if ($product->quote_quantity <= 0) {
                continue;
            }
            if ($product->job->hideTotals()) {
                continue;
            }
            $winFactor = $product->job->status == 'job/quote' ? ($product->job->quote_win_chance / 100) : 1;
            $productPercent = ($product->quote_factor_price - $product->quote_discount_price) / $product->job->quote_wholesale_price;
            $cost += $product->job->quote_total_cost * $winFactor * $productPercent;
            $sell += $product->job->getReportTotal() * $winFactor * $productPercent;
        }
        $margin = $sell - $cost;
        $attributes = [];
        $attributes[] = [
            'label' => Yii::t('app', 'Cost'),
            'value' => $cost,
            'format' => ['decimal', 2],
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Sell'),
            'value' => $sell,
            'format' => ['decimal', 2],
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Margin'),
            'value' => number_format($margin, 2) . ' <span class="label label-default">' . round(($sell ? $margin / $sell : 0) * 100) . '%</span>',
            'format' => 'raw',
        ];
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>

    </div>
</div>
