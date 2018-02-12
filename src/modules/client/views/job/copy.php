<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 * @var app\models\Job $modelCopy
 */

$this->title = $modelCopy->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $modelCopy->id . ': ' . $modelCopy->name, 'url' => ['view', 'id' => $modelCopy->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Copy');
?>
<div class="job-copy">

    <?= $this->render('_menu', ['model' => $modelCopy]); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
        'modelCopy' => $modelCopy,
    ]); ?>


    <a href="http://google.com" class="btn btn-primary"></a>

</div>
