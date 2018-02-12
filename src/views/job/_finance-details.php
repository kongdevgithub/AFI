<?php

use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */
?>

<div class="row">
    <div class="col-md-6">
        <?php
        $attributes = [];
        $attributes[] = [
            'attribute' => 'quote_freight_price',
            'value' => number_format($model->quote_freight_price, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'attribute' => 'quote_surcharge_price',
            'value' => number_format($model->quote_surcharge_price, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'attribute' => 'quote_discount_price',
            'value' => number_format($model->quote_discount_price, 2),
            'format' => 'raw',
        ];
        echo DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
            'options' => ['class' => 'table table-condensed detail-view'],
        ]);
        ?>
    </div>
    <div class="col-md-6">
        <?php
        $attributes = [];
        $attributes[] = [
            'attribute' => 'invoice_sent',
            'format' => 'date',
        ];
        $attributes[] = [
            'attribute' => 'quote_discount_price',
            'value' => number_format($model->quote_discount_price, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'attribute' => 'invoice_amount',
            'value' => number_format($model->invoice_amount, 2),
            'format' => 'raw',
        ];
        $attributes[] = [
            'attribute' => 'invoice_reference',
        ];
        $attributes[] = [
            'attribute' => 'invoice_paid',
            'format' => 'date',
        ];
        echo DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
            'options' => ['class' => 'table table-condensed detail-view'],
        ]);
        ?>
    </div>
</div>