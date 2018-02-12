<?php

use app\components\freight\Freight;
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

        // under 30% margin
        if (!$model->checkPriceMargin()) {
            echo '<div class="alert alert-danger">' . Yii::t('app', 'The price for this Job has less than 30% margin!') . '</div>';
        }

        $attributes = [];

        if (!$model->hasForkQuantityProducts()) {

            if (Y::user()->can('_view_cost_prices')) {
                $attributes[] = [
                    'label' => Yii::t('app', 'Cost'),
                    'attribute' => 'quote_total_cost',
                    'format' => ['decimal', 2],
                ];
            }

            $extra = Y::user()->can('_view_cost_prices') ? ' <span class="label label-default">+' . number_format($model->quote_wholesale_price - $model->quote_total_cost, 2) . '</span>' : '';
            $attributes[] = [
                'label' => Yii::t('app', 'Wholesale'),
                'value' => number_format($model->quote_wholesale_price, 2) . $extra,
                'format' => 'raw',
            ];

            $wholeSaleMarkup = ' <span class="label label-default">+' . number_format($model->quote_retail_price - $model->quote_wholesale_price, 2) . '</span>';
            $attributes[] = [
                'label' => Yii::t('app', 'Retail'),
                'value' => number_format($model->quote_retail_price, 2) . $wholeSaleMarkup,
                'format' => 'raw',
            ];

            $extra = '';
            if (Y::user()->can('app_job_freight', ['route' => true])) {
                $extra = ' ' . Html::a('<span class="fa fa-pencil"></span>', ['/job/freight', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']);
            }
            if ($model->freight_quote_requested_at && !$model->freight_quote_provided_at) {
                $extra = ' ' . Html::tag('span', '', ['class' => 'fa fa-exclamation-triangle', 'title' => Yii::t('app', 'Freight Quote Requested')]);
            }

            //$freightWeight = ' (' . ceil($model->quote_weight) . 'kg)';
            $freight = [
                number_format($model->quote_freight_price, 2)
            ];
            $carriers = Freight::getCarrierNames();
            if ($model->freight_method)
                if ($carriers && isset($carriers[$model->freight_method]))
                    $freight[] = $carriers[$model->freight_method];
                else
                    $freight[] = $model->freight_method;
            if ($model->freight_notes)
                $freight[] = $model->freight_notes;
            $attributes[] = [
                'label' => Yii::t('app', 'Freight'),
                'value' => implode(' - ', $freight) . $extra,
                'format' => 'raw',
            ];

            $extra = '';
            $extra = Y::user()->can('app_job_surcharge', ['route' => true]) ? ' ' . Html::a('<span class="fa fa-pencil"></span>', ['/job/surcharge', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']) : '';
            $attributes[] = [
                'label' => Yii::t('app', 'Surcharge'),
                'value' => number_format($model->quote_surcharge_price, 2) . ' ' . $extra,
                'format' => 'raw',
            ];

            $extra = '';
            if (Y::user()->can('app_job_discount', ['route' => true])) {
                $extra = Html::a('<span class="fa fa-pencil"></span>', ['/job/discount', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']);
            } else {
                $extra = Html::a('<span class="fa fa-times"></span>', ['/job/discount-remove', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['data-confirm' => Yii::t('app', 'Are you sure?')]);
            }
            $productDiscount = 0;
            foreach ($model->products as $product) {
                $productDiscount += $product->quote_discount_price * $model->quote_markup;
            }
            $attributes[] = [
                'label' => Yii::t('app', 'Discount'),
                'value' => number_format($model->quote_discount_price, 2) . ($productDiscount ? ' <span class="label label-default">' . number_format($productDiscount, 2) . ' ' . Yii::t('app', 'in products') . '</span>' : '') . ' ' . $extra,
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


