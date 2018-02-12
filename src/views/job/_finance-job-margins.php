<?php

use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Yii::t('app', 'Job Margins'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        $cost = $model->quote_total_cost;
        $sell = $model->getReportTotal();
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
            'model' => $model,
            'attributes' => $attributes,
        ]);
        ?>

    </div>
</div>





