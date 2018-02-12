<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\CompanyForm $model
 */

$this->title = $model->company->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->company->name, 'url' => ['view', 'id' => $model->company->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-update">

    <?= $this->render('_menu', ['model' => $model->company]); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
