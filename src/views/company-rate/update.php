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
 * @var app\models\form\CompanyRateForm $model
 */
$this->title = Yii::t('app', 'Company Rate') . ': ' . $model->companyRate->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Company Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->companyRate->id, 'url' => ['view', 'id' => $model->companyRate->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-rate-update">

    <?php echo $this->render('_menu', ['model' => $model->companyRate]); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
