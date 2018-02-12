<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$versions = [];
foreach ($model->getForkVersionVids() as $job_id => $vid) {
    $versions[] = Html::a($vid, ['/job/view', 'id' => $job_id]);
}
if (count($versions) <= 1) {
    return;
}
?>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h3 class="panel-title"><?= Yii::t('app', 'Versions') ?></h3>
        </div>
    </div>
    <div class="panel-body">
        <?= implode(', ', $versions) ?>
    </div>
</div>

