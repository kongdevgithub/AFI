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
        foreach ($jobs as $job) {
            if ($job->hideTotals()) {
                continue;
            }
            $factor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
            $jobDiscount += $job->quote_discount_price * $factor;
            $productDiscount += $job->getProductDiscount() * $factor;
            $maximumDiscount += $job->quote_maximum_discount_price * $job->quote_markup * $factor;
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
