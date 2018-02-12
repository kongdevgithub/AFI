<?php

use app\components\BulkQuoteHelper;
use app\components\MenuItem;
use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\jobs\BaseJobQuote;
use app\components\quotes\products\BaseProductQuote;
use kartik\grid\GridView;
use yii\bootstrap\Nav;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Quote Classes');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();
?>


<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Job Quote Classes'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $attributes = [];
        foreach (BaseJobQuote::opts() as $quoteClass => $name) {
            /** @var BaseJobQuote $quote */
            $quote = new $quoteClass;
            $attributes[] = [
                'label' => '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>',
                'value' => $quote->getDescription(),
                'format' => 'raw',
            ];
        }
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Product Quote Classes'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $attributes = [];
        foreach (BaseProductQuote::opts() as $quoteClass => $name) {
            /** @var BaseProductQuote $quote */
            $quote = new $quoteClass;
            $attributes[] = [
                'label' => '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>',
                'value' => $quote->getDescription(),
                'format' => 'raw',
            ];
        }
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Item Quote Classes'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $attributes = [];
        foreach (BaseItemQuote::opts() as $quoteClass => $name) {
            /** @var BaseItemQuote $quote */
            $quote = new $quoteClass;
            $attributes[] = [
                'label' => '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>',
                'value' => $quote->getDescription(),
                'format' => 'raw',
            ];
        }
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Component Quote Classes'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        $attributes = [];
        foreach (BaseComponentQuote::opts() as $quoteClass => $name) {
            /** @var BaseComponentQuote $quote */
            $quote = new $quoteClass;
            $attributes[] = [
                'label' => '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>',
                'value' => $quote->getDescription(),
                'format' => 'raw',
            ];
        }
        echo DetailView::widget([
            'model' => false,
            'attributes' => $attributes,
        ]);
        ?>
    </div>
</div>

