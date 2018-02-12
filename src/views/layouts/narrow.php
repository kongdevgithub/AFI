<?php

use app\components\MenuItem;
use app\components\ReturnUrl;
use app\widgets\Nav;
use cornernote\shortcuts\Y;
use dmstr\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

if (Yii::$app->request->isAjax) {
    echo $this->render('ajax', ['content' => $content]);
    return;
}
\app\assets\AppAsset::register($this);
if (Yii::$app->request->get('iframe')) {
    echo $this->render('ajax', ['content' => $content]);
    return;
}
if (Yii::$app->user->isGuest) {
    $skin = 'skin-black';
    $background = '';
} else {
    $identity = Yii::$app->user->identity;
    $skin = !empty($identity->skin) ? $identity->skin : 'skin-black';
    $background = $identity->background ? ' ' . $identity->background : '';
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('_head') ?>
</head>
<body class="hold-transition layout-top-nav fixed <?= $skin ?>">
<?php $this->beginBody() ?>

<div class="wrapper">
    <?php if (empty($this->params['hide-navbar']) || !$this->params['hide-navbar']) { ?>
        <header class="main-header">
            <nav id="navbar" class="navbar navbar-fixed-top" role="navigation">
                <div class="container">
                    <?= $this->render('_navbar', ['skin' => $skin]) ?>
                </div>
            </nav>
        </header>
    <?php } else {
        $this->registerCss('.fixed .content-wrapper { padding-top: 0px; }');
    } ?>
    <div class="content-wrapper<?= $background ?>">
        <div class="container">
            <?= $this->render('_site-notes') ?>
        </div>
        <?php
        $header = $this->render('_content-header');
        if ($header) {
            ?>
            <section class="content-header">
                <div class="container">
                    <?= $header ?>
                </div>
            </section>
            <?php
        }
        $breadcrumbs = $this->render('_content-breadcrumbs');
        if ($breadcrumbs) {
            ?>
            <section class="content-breadcrumbs">
                <div class="container">
                    <?= $breadcrumbs ?>
                </div>
            </section>
            <?php
        }
        ?>
        <section class="content">
            <div class="container">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </section>
    </div>
    <?php if (empty($this->params['hide-footer']) || !$this->params['hide-footer']) { ?>
        <?= $this->render('_footer'); ?>
    <?php } ?>
</div>
<?= $this->render('_tawk'); ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
