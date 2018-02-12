<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/fccccf4deb34aed738291a9c38e87215
 *
 * @package default
 */


use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\SignageWayfinding $model
 */
$this->title = Yii::t('goldoc', 'Signage Wayfinding') . ': ' . Yii::t('goldoc', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Signage Wayfindings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Create');
?>
<div class="signage-wayfinding-create">

    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
