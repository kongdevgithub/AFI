<?php

use app\models\Pickup;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\PickupSearch $searchModel
 */

$this->title = Yii::t('app', 'Pickups');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="pickup-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>