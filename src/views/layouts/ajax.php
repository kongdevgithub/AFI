<?php
use app\components\ReturnUrl;
use yii\helpers\Html;
use dmstr\widgets\Alert;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <?= $this->render('_head') ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <?= Alert::widget() ?>
    <?= $content ?>
    <div style="display:none;">
        <?= $this->render('_footer'); ?>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>