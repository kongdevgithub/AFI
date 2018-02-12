<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$attributes = [];
if ($model->copyJob) {
    $attributes[] = [
        'attribute' => 'copy_job_id',
        'value' => $model->copyJob->getLink() . ' ' . $model->copyJob->name,
        'format' => 'raw',
    ];
}
$forkedTo = [];
foreach ($model->copyJobs as $copyJob) {
    $forkedTo[] = $copyJob->getLink() . ' ' . $copyJob->name;
}
if ($forkedTo) {
    $attributes[] = [
        'label' => Yii::t('app', 'Copied To'),
        'value' => implode('<br>', $forkedTo),
        'format' => 'raw',
    ];
}
if (!$attributes) {
    return;
}
?>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h3 class="panel-title"><?= Yii::t('app', 'Copies') ?></h3>
        </div>
    </div>
    <div class="panel-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
        ]) ?>
    </div>
</div>

