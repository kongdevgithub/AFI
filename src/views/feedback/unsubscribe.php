<?php
/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 * @var string $key
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'You have been unsubscribed!');
$this->params['heading'] = '';

?>

<div class="feedback-unsubscribe">

    <div class="jumbotron">
        <h1><?= $this->title ?></h1>
        <h2><?= Yii::t('app', 'We won\'t be sending you any more feedback request emails.') ?></h2>
    </div>

    <?php /* ?>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'But before you leave...'); ?></h3>
        </div>
        <div class="box-body">
            <p><?= Yii::t('app', 'We\'d like you to meet Brendon Rowse'); ?>:</p>
            <?= Html::img('https://media.licdn.com/mpr/mpr/shrinknp_400_400/AAEAAQAAAAAAAAPsAAAAJGZkNzg4YTM0LTBhMTYtNDkwNC1iMmY1LTZmOGIxODU4YTFiNw.jpg', ['class' => 'thumbnail']) ?>
            <p><?= Yii::t('app', 'He\'s the person who though you would like to receive these emails...'); ?></p>
            <?= Html::a(Yii::t('app', 'Punish Brendon!'), ['feedback/punish', 'id' => $model->id, 'key' => $key], ['class' => 'btn btn-primary btn-lg modal-remote']) ?>
        </div>
    </div>
    <?php */ ?>

</div>
