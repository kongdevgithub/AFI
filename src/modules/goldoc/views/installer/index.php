<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\InstallerSearch $searchModel
 */

$this->title = Yii::t('goldoc', 'Installers');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="installer-index">

    <?=  $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>