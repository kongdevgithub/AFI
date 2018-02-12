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
 * @var app\modules\goldoc\models\search\SupplierSearch $searchModel
 */
$this->title = Yii::t('goldoc', 'Suppliers');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="supplier-index">

    <?php echo $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
