<?php

use app\components\quotes\components\BaseComponentQuote;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ComponentTypeSearch $searchModel
 */

$this->title = Yii::t('app', 'Component Types');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="component-type-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>