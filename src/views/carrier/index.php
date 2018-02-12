<?php

use app\components\ReturnUrl;
use app\models\Carrier;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CarrierSearch $searchModel
 */

$this->title = Yii::t('app', 'Carriers');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="carrier-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>