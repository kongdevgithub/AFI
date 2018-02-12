<?php

use app\models\User;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Note $model
 */

$showActions = isset($showActions) ? $showActions : true;
?>

<div class="box <?= $model->important ? 'box-danger' : 'box-primary' ?> box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $model->title; ?></h3>
        <?php if ($showActions) { ?>
            <div class="box-tools pull-right">
                <?php if ($model->model_name != Yii::$app->className() || Yii::$app->user->can('manager')) { ?>
                    <?= Html::a('<i class="fa fa-pencil"></i>', ['/note/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'btn btn-box-tool modal-remote',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-trash"></i>', ['/note/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'data-confirm' => Yii::t('app', 'Are you sure?'),
                        'data-method' => 'post',
                        'class' => 'btn btn-box-tool',
                    ]) ?>
                    <?php if ($model->model_name != Yii::$app->className()) { ?>
                        <?= Html::a('<i class="fa fa-arrows sortable-handle"></i>', null, [
                            'class' => 'btn btn-box-tool',
                        ]) ?>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <div class="box-body">
        <?= Yii::$app->formatter->asNtext($model->body) ?>
    </div>
    <div class="box-footer with-border" style="padding: 0 0 0 10px;">
        <small style="color: #7c7c7c">
            <?php
            $user = User::findOne($model->created_by);
            if ($user) {
                echo Yii::t('app', 'by') . ' ' . $user->label . ' ';
            }
            echo Yii::t('app', 'on') . ' ' . Yii::$app->formatter->asDatetime($model->created_at);
            ?>
        </small>
    </div>
</div>
