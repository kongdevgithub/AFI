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
        'value' => $model->redoJob->getLink() . ' ' . $model->redoJob->name . '<br>' . Yii::$app->formatter->asNtext($model->redo_reason),
        'format' => 'raw',
    ];
}
$redoneTo = [];
foreach ($model->redoJobs as $redoJob) {
    $redoneTo[] = $redoJob->getLink() . ' ' . $redoJob->name . '<br>' . Yii::$app->formatter->asNtext($redoJob->redo_reason);
}
if ($redoneTo) {
    $attributes[] = [
        'label' => Yii::t('app', 'Redone To'),
        'value' => implode('<br>', $redoneTo),
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

