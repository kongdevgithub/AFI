<?php

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\SignageWayfindingSearch $searchModel
 */
$this->title = Yii::t('goldoc', 'Signage Wayfindings');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="signage-wayfinding-index">

    <?php echo $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
