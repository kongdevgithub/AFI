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
<div class="job-preview">

    <?php
    $items = [];
    $items[] = [
        'label' => Yii::t('app', 'View Job'),
        'url' => ['//job/view', 'id' => $model->id],
    ];
    echo Nav::widget([
        'options' => ['class' => 'nav-tabs'],
        'encodeLabels' => false,
        'items' => $items,
    ]);
    ?>

    <?= $this->render('_preview-products', ['model' => $model]) ?>

</div>

