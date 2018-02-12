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
        <h3 class="panel-title"><?= Yii::t('app', 'Discounts'); ?></h3>
    </div>
    <div class="panel-body">
        <?php
        // discounts applied
        $attributes = [];
        if ($model->quote_discount_price != 0) {
            $attributes[] = [
                'label' => Yii::t('app', 'Job Discount'),
                'value' => number_format($model->quote_discount_price, 2),
                'format' => 'raw',
            ];
        }
        $productDiscount = $model->getProductDiscount();
        if ($productDiscount != 0) {
            $attributes[] = [
                'label' => Yii::t('app', 'Product Discount'),
                'value' => number_format($productDiscount, 2),
                'format' => 'raw',
            ];
        }
        if ($model->quote_discount_price + $productDiscount != 0) {
            $attributes[] = [
                'label' => Yii::t('app', 'Total Discount'),
                'value' => number_format($model->quote_discount_price + $productDiscount, 2),
                'format' => 'raw',
            ];
        }
        $attributes[] = [
            'label' => Yii::t('app', 'Maximum Discount'),
            'value' => number_format($model->quote_maximum_discount_price, 2),
            'format' => 'raw',
        ];
        if ($model->quote_maximum_discount_price - $model->quote_discount_price - $productDiscount != 0) {
            $attributes[] = [
                'label' => Yii::t('app', 'Total Offset'),
                'value' => number_format($model->quote_maximum_discount_price - $model->quote_discount_price - $productDiscount, 2),
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



