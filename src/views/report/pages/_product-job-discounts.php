<?php
/**
 * @var View $this
 * @var Job[] $jobs
 */

use app\models\Job;
use yii\web\View;
use yii\widgets\DetailView;

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Yii::t('app', 'Discounts'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $jobDiscount = 0;
        $productDiscount = 0;
        $maximumDiscount = 0;
        foreach ($products as $product) {
            if ($product->quote_quantity <= 0) {
                continue;
            }
            if ($product->job->hideTotals()) {
                continue;
            }
            $winFactor = $product->job->status == 'job/quote' ? ($product->job->quote_win_chance / 100) : 1;
            $productPercent = ($product->quote_factor_price - $product->quote_discount_price) / $product->job->quote_wholesale_price;
            $jobDiscount += $product->job->quote_discount_price * $winFactor * $productPercent;
            $productDiscount += $product->job->getProductDiscount() * $winFactor * $productPercent;
            $maximumDiscount += $product->job->quote_maximum_discount_price * $product->job->quote_markup * $winFactor * $productPercent;
        }
        $attributes = [];
        $attributes[] = [
            'label' => Yii::t('app', 'Job Discount'),
            'value' => number_format($jobDiscount, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Product Discount'),
            //'value' => number_format($model->getProductDiscount(), 2),
            'value' => number_format($productDiscount, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Total Discount'),
            'value' => number_format($jobDiscount + $productDiscount, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Maximum Discount'),
            'value' => number_format($maximumDiscount, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'label' => Yii::t('app', 'Total Offset'),
            'value' => number_format($maximumDiscount - $jobDiscount - $productDiscount, 2),
            'format' => 'raw',
        ];
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>
