<?php

use app\models\Rollout;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\RolloutSearch $searchModel
 */

$this->title = Yii::t('app', 'Rollouts');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rollout-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>