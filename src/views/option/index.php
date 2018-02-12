<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\OptionSearch $searchModel
 */

$this->title = Yii::t('app', 'Options');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="option-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>