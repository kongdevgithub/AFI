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
 * @var app\modules\goldoc\models\Sponsor $model
 */
$this->title = Yii::t('goldoc', 'Sponsor') . ': ' . Yii::t('goldoc', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Sponsors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Create');
?>
<div class="sponsor-create">

    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
