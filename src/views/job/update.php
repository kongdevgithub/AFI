<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->id . ': ' . $model->job->name, 'url' => ['view', 'id' => $model->job->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="job-update">

    <?= $this->render('_menu', ['model' => $model->job]); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
