<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/21d7aebfe01f0101a823e3ac0743e205
 *
 * @package default
 */


use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Item $model
 */
$this->title = Yii::t('goldoc', 'Item') . ': ' . $model->name;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Copy');
?>
<div class="item-copy">

    <?php echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
