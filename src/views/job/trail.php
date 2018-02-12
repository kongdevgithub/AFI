<?php

use app\models\Log;
use app\models\User;
use bedezign\yii2\audit\models\AuditEntry;
use cebe\gravatar\Gravatar;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Trail');

?>
<div class="job-log">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Audit Trail'); ?></h3>
        </div>
        <div class="box-body">

            <?= $this->render('/layouts/_audit_trails', [
                'query' => $model->getAuditTrails(),
                'columns' => ['user_id', 'entry_id', 'action', 'model', 'model_id', 'field', 'diff', 'created'],
                //'params' => [
                //    'AuditTrailSearch' => [],
                //],
            ]) ?>

        </div>
    </div>

</div>

