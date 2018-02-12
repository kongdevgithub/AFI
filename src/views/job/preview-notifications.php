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

$ru = isset($ru) ? $ru : ReturnUrl::getToken();
?>
<div class="job-preview-notifications">

    <?php if ($model->notifications) { ?>
        <div class="box">
            <div class="box-header box-solid">
                <h3 class="box-title"><?= Yii::t('app', 'Job'); ?></h3>
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

    <?php foreach ($model->products as $product) { ?>
        <?php if ($product->notifications) { ?>
            <div class="box">
                <div class="box-header box-solid">
                    <h3 class="box-title"><?= $product->name ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    foreach ($product->notifications as $notification) {
                        echo $this->render('/notification/_view', ['model' => $notification, 'ru' => $ru]);
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <?php foreach ($product->items as $item) { ?>
            <?php if ($item->notifications) { ?>
                <div class="box">
                    <div class="box-header box-solid">
                        <h3 class="box-title"><?= $item->name ?></h3>
                    </div>
                    <div class="box-body">
                        <?php
                        foreach ($item->notifications as $notification) {
                            echo $this->render('/notification/_view', ['model' => $notification, 'ru' => $ru]);
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

    <?php } ?>

</div>

