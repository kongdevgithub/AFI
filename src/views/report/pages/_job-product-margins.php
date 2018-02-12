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
        <h3 class="panel-title"><?= Yii::t('app', 'Product Margins'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $total = 0;
        $count = 0;
        $attributes = [];
        foreach ($jobs as $job) {
            if ($job->hideTotals()) {
                continue;
            }
            $factor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
            foreach ($job->products as $product) {
                $value = (1 - $product->quote_factor) * $product->quote_total_price * $job->quote_markup * -1;
                $total += $value * $factor;
                foreach ($product->items as $item) {
                    $count += $item->quantity * $factor;
                }
                //$attributes[] = [
                //    'label' => $product->name,
                //    'value' => number_format($value, 2) . ' <span class="label label-default">' . ($product->quote_factor * 1) . '</span>',
                //    'format' => 'raw',
                //];
            }
        }
        $attributes[] = [
            'label' => Yii::t('app', 'Total  Offset'),
            'value' => number_format($total, 2),
            'format' => 'raw',
        ];
        //$attributes[] = [
        //    'label' => Yii::t('app', 'Count'),
        //    'value' => number_format($count, 2),
        //    'format' => 'raw',
        //];
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>

    </div>
</div>

