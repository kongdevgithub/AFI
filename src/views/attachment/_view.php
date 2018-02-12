<?php

use app\models\User;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Attachment $model
 */

$showActions = isset($showActions) ? $showActions : true;

// get $img
$src = $model->getFileUrl('100x100');
$link = $model->getFileUrl();
if ($src) {
    $img = Html::a(Html::img($src, [
        'height' => '80',
    ]), $link, [
        'target' => '_blank',
        'data-fancybox' => 'gallery-' . $model->id,
    ]);
} else {
    $img = Html::a($model->filename, $link, [
        'target' => '_blank',
    ]);
}

?>

<div class="info-box bg-aqua">
    <div class="info-box-image"><?= $img ?></div>
    <div class="info-box-content">

        <?php if ($showActions) { ?>
            <div class="info-box-tools pull-right">
                <?php
                $links = [];
                if (Yii::$app->user->can('app_attachment_update', ['route' => true])) {
                    $links[] = Html::a('<i class="fa fa-pencil"></i>', ['/attachment/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'modal-remote',
                    ]);
                }
                if (Yii::$app->user->can('app_attachment_delete', ['route' => true])) {
                    $links[] = Html::a('<i class="fa fa-trash"></i>', ['/attachment/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'data-confirm' => Yii::t('app', 'Are you sure?'),
                        'data-method' => 'post',
                    ]);
                }
                if (Yii::$app->user->can('app_attachment_sort', ['route' => true])) {
                    $links[] = Html::a('<i class="fa fa-arrows sortable-handle"></i>');
                }
                echo Html::ul($links, ['class' => 'list-inline', 'encode' => false]);
                ?>
            </div>
            <hr style="margin: 1px 0; clear: right;">
        <?php } ?>

        <div class="info-box-number">
            <?= $model->filename . '.' . $model->extension; ?>
        </div>
        <div class="small">
            <?= $model->notes; ?>
        </div>
        <div class="small">
            <?php
            $user = User::findOne($model->created_by);
            if ($user) {
                echo Yii::t('app', 'by') . ' ' . $user->label . ' ';
            }
            echo Yii::t('app', 'on') . ' ' . Yii::$app->formatter->asDatetime($model->created_at);
            ?>
        </div>

    </div>
</div>
