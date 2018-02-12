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
 * @var app\models\Product $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Log');

?>
<div class="product-log">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <ul class="timeline">
        <?php
        $_date = false;
        foreach ($model->getLogs()->all() as $log) {
            $user = $log->created_by ? User::findOne($log->created_by) : false;
            $date = Yii::$app->formatter->asDate($log->created_at, 'long');
            if ($date != $_date) {
                $_date = $date;
                ?>
                <li class="time-label">
                    <span class="bg-green"><?= Yii::$app->formatter->asDate($log->created_at, 'long') ?></span>
                </li>
                <?php
            }
            ?>
            <li>
                <i class="fa" title="<?= $user ? $user->label : 'system' ?>" data-toggle="tooltip"><?= $user ? $user->getAvatar(32) : User::getSystemAvatar(32) ?></i>
                <div class="timeline-item collapsed-box">
                    <span class="time">
                        <i class="fa fa-clock-o"></i> <?= Yii::$app->formatter->asTime($log->created_at) ?>
                        <?php
                        if (Yii::$app->user->can('admin')) {
                            echo Html::a('<i class="fa fa-info-circle"></i> ' . $log->audit_entry_id, ['//audit/entry/view', 'id' => $log->audit_entry_id]);
                        }
                        ?>
                    </span>
                    <h3 class="timeline-header">
                        <?= $user ? $user->label : 'system' ?>
                    </h3>
                    <div class="timeline-body">
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <?= $log->message ?>
                        <span class="label label-default"><?= str_replace('app\\models\\', '', $log->model_name) . '.' . $log->model_id ?></span>
                    </div>
                    <div class="timeline-footer" style="display:none;">
                        <?= Html::ul($log->getAuditTrails(), ['encode' => false]) ?>
                    </div>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>
        $("[data-widget='collapse']").click(function () {
            //Find the box parent
            var box = $(this).parents(".timeline-item").first();
            //Find the body and the footer
            var bf = box.find(".timeline-footer");
            if (!box.hasClass("collapsed-box")) {
                box.addClass("collapsed-box");
                bf.slideUp();
            } else {
                box.removeClass("collapsed-box");
                bf.slideDown();
            }
        });
    </script>
    <?php \app\widgets\JavaScript::end() ?>

</div>

