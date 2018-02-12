<?php
/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 * @var string $key
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Punish Brendon!');
$this->params['heading'] = '';

?>

<div class="feedback-punish">

    <h2><?= Yii::t('app', 'Comming soon...') ?></h2>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'That was pretty mean... I hope you\'re happy.'); ?></h3>
        </div>
        <div class="box-body">
            <p><?= Yii::t('app', 'Want to make it up to Brendon?'); ?></p>
            <?= Html::a(Yii::t('app', 'Resubscribe?'), ['feedback/resubscribe', 'id' => $model->id, 'key' => $key], ['class' => 'btn btn-primary modal-remote']) ?>
        </div>
    </div>
</div>
