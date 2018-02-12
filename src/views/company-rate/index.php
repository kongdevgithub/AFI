<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/a0a12d1bd32eaeeb8b2cff56d511aa22
 *
 * @package default
 */


/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\CompanyRateSearch $searchModel
 */
$this->title = Yii::t('app', 'Company Rates');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-rate-index">

    <?php echo  $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
