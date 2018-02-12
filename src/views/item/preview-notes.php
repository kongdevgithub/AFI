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
?>
<div class="item-preview-notes">

    <?php if ($model->product->notes) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Product'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->product->notes as $note) {
                    echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->product->job->notes) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Job'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->product->job->notes as $note) {
                    echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->product->job->company->notes) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Company'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->product->job->company->notes as $note) {
                    echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

</div>
