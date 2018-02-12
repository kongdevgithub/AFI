<?php

use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Product $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . Yii::t('goldoc', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Create');
?>
<div class="product-create">

    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
