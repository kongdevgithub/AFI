<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/fcd70a9bfdf8de75128d795dfc948a74
 *
 * @package default
 */


use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\SignageWayfinding $model
 */
$this->title = Yii::t('goldoc', 'Signage Wayfinding') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Signage Wayfindings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Update');
?>
<div class="signage-wayfinding-update">

    <?php echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
