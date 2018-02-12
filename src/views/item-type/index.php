<?php

use app\components\quotes\items\BaseItemQuote;
use app\models\ItemType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ItemTypeSearch $searchModel
 */

$this->title = Yii::t('app', 'Item Types');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="item-type-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>