<?php

use app\components\Helper;
use app\models\Unit;
use app\components\ReturnUrl;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = $this->title;

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="item-preview-notifications">

    <?php if ($model->notifications) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Item'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->notifications as $notification) {
                    echo $this->render('/notification/_view', ['model' => $notification, 'ru' => $ru]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->product->notifications) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Product'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->product->notifications as $notification) {
                    echo $this->render('/notification/_view', ['model' => $notification, 'ru' => $ru]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->product->job->notifications) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Job'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->product->job->notifications as $notification) {
                    echo $this->render('/notification/_view', ['model' => $notification, 'ru' => $ru]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

</div>
