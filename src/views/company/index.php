<?php

use app\models\AccountTerm;
use app\models\Company;
use app\models\Industry;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\User;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CompanySearch $searchModel
 */

$this->title = Yii::t('app', 'Companies');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>