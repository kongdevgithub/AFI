<?php

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\SignageFaSearch $searchModel
 */
$this->title = Yii::t('goldoc', 'Signage FAs');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="signage-fa-index">

    <?php echo $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
