<?php
/**
 * @var $this \yii\web\View
 */

use app\components\ReturnUrl;
use bedezign\yii2\audit\Audit;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<footer class="main-footer clearfix small">
    <div class="pull-left">
        <?= Yii::$app->formatter->asDatetime(time()); ?>
    </div>
    <div class="pull-right">
        <?= $this->render('@bedezign/yii2/audit/views/_audit_entry_id'); ?>
        <?= ReturnUrl::getRequestToken() ? Html::tag('span', 'RU: ' . ReturnUrl::getRequestToken(), [
            'class' => 'label label-default',
            'data-toggle' => 'tooltip',
            'title' => ReturnUrl::getUrl(),
        ]) : '' ?>
    </div>
</footer>
