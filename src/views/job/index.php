<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\JobSearch $searchModel
 */

$this->title = Yii::t('app', 'Jobs');
//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="job-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>