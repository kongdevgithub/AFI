<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/d4b4964a63cc95065fa0ae19074007ee
 *
 * @package default
 */


use app\components\fields\BaseField;
use app\models\ProductType;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Colour $model
 */
$this->title = Yii::t('goldoc', 'Colour') . ': ' . $model->name;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Colours'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="colour-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Colour') ?></h3>
        </div>
        <div class="box-body">
            <?php echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			'code',
			'name',
		],
	]); ?>
        </div>
    </div>

</div>
