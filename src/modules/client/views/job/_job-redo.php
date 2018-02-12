<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$attributes = [];
if ($model->redoJob) {
    $attributes[] = [
        'attribute' => 'redo_job_id',
        'value' => $model->redoJob->getLink() . ' ' . $model->redoJob->name,
        'format' => 'raw',
    ];
}
$forkedTo = [];
foreach ($model->redoJobs as $redoJob) {
    $forkedTo[] = $redoJob->getLink() . ' ' . $redoJob->name;
}
if ($forkedTo) {
    $attributes[] = [
        'label' => Yii::t('app', 'Redone To'),
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
            <h3 class="panel-title"><?= Yii::t('app', 'Redos') ?></h3>
        </div>
    </div>
    <div class="panel-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => $attributes,
        ]) ?>
    </div>
</div>

