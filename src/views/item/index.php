<?php

use app\models\Item;
use kartik\grid\GridView;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ItemSearch $searchModel
 */

$this->title = Yii::t('app', 'Items');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="item-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>