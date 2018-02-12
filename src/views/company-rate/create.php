<?php

use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\models\form\CompanyRateForm $model
 */
$this->title = Yii::t('app', 'Company Rate') . ': ' . Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Company Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>
<div class="company-rate-create">

    <?php echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
