<?php

use app\models\Address;
use app\models\Package;
use app\models\Product;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-sort">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <?= $this->render('_sort-products', ['model' => $model]) ?>

</div>
