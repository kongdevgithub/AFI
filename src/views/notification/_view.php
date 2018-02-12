<?php

use app\models\User;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Notification $model
 */

$ru = isset($ru) ? $ru : ReturnUrl::getToken();
?>

<div class="box box-<?= $model->type ?> box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $model->title; ?></h3>
        <div class="box-tools pull-right">
            <?= Html::a('<i class="fa fa-trash"></i>', ['/notification/delete', 'id' => $model->id, 'ru' => $ru], [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
                'class' => 'btn btn-box-tool',
            ]) ?>
        </div>
    </div>
    <div class="box-body">
        <?= $model->body ?>
    </div>
    <div class="box-footer with-border">
        <small style="color: #7c7c7c">
            <?php
            $user = User::findOne($model->created_by);
            if ($user) {
                echo Yii::t('app', 'by') . ': ' . $user->label . ' ';
            }
            echo Yii::t('app', 'on') . ' ' . Yii::$app->formatter->asDatetime($model->created_at);
            ?>
        </small>
    </div>
</div>
