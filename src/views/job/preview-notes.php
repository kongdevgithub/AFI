<?php

use app\models\Component;
use app\models\ItemType;
use app\models\Option;
use app\widgets\Nav;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-preview-notes">

    <?php if ($model->notes) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Job'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->notes as $note) {
                    echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($model->company->notes) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Company'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                foreach ($model->company->notes as $note) {
                    echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                }
                ?>
            </div>
        </div>
    <?php } ?>

    <?php foreach ($model->products as $product) { ?>
        <?php if ($product->notes) { ?>
            <div class="box">
                <div class="box-header box-solid">
                    <h3 class="box-title"><?= $product->name ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    foreach ($product->notes as $note) {
                        echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
                    }
                    ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

</div>

