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
        <h3 class="panel-title"><?= Yii::t('app', 'Totals') ?></h3>
    </div>
    <div class="panel-body">
        <?php

        $attributes = [];

        if (!$model->hasForkQuantityProducts()) {

            $attributes[] = [
                'label' => Yii::t('app', 'Retail'),
                'value' => number_format($model->quote_retail_price, 2),
                'format' => 'raw',
            ];
            $attributes[] = [
                'label' => Yii::t('app', 'GST'),
                'value' => number_format($model->quote_tax_price, 2),
                'format' => 'raw',
            ];
            $attributes[] = [
                'label' => Yii::t('app', 'TOTAL'),
                'value' => number_format($model->quote_total_price - $model->quote_tax_price, 2) . ' <span class="label label-default">' . number_format($model->quote_total_price, 2) . 'inc</span>',
                'format' => 'raw',
            ];

        } else {
            $attributes[] = [
                'label' => Yii::t('app', 'INFORMATION'),
                'value' => Yii::t('app', 'Totals are not displayed while there are forked quantities.'),
                'format' => 'raw',
            ];
        }

        echo DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>


