<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 */

$this->title = Yii::t('app', 'Create Quote/Job');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
