<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\PackageSearch $searchModel
 */

$this->title = Yii::t('app', 'Packages');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="package-index">

    <?php echo $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

</div>