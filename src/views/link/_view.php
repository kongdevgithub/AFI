<?php

use app\models\User;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Link $model
 */

$showActions = isset($showActions) ? $showActions : true;
?>

<div class="box box-default box-solid">
    <div class="box-body">
        <div>
            <?= Html::a($model->title, $model->url, ['target' => '_blank']) ?>
        </div>
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
        <?php if ($showActions) { ?>
            <div class="box-tools pull-right">
                <?php if ($model->model_name != Yii::$app->className() || Yii::$app->user->can('manager')) { ?>
                    <?= Html::a('<i class="fa fa-pencil"></i>', ['/link/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'btn btn-box-tool modal-remote',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-trash"></i>', ['/link/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
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
</div>
