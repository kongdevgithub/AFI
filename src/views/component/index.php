<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ComponentType;
use kartik\grid\GridView;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ComponentSearch $searchModel
 */

$this->title = Yii::t('app', 'Components');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="component-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>