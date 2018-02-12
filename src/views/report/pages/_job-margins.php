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
        <h3 class="panel-title"><?= Yii::t('app', 'Job Margins'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $cost = 0;
        $sell = 0;
        foreach ($jobs as $job) {
            if ($job->hideTotals()) {
                continue;
            }
            $factor = $job->status == 'job/quote' ? ($job->quote_win_chance / 100) : 1;
            $cost += $job->quote_total_cost * $factor;
            $sell += $job->getReportTotal() * $factor;
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
