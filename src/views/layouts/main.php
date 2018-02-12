<?php

use app\components\ReturnUrl;
use dmstr\widgets\Alert;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

if (Yii::$app->request->isAjax || Yii::$app->request->get('ajax')) {
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
                <?php
                echo $this->render('_navbar', ['skin' => $skin]);
                ?>
            </nav>
        </header>
    <?php } else {
        $this->registerCss('.fixed .content-wrapper { padding-top: 0px; }');
    } ?>
    <div class="content-wrapper<?= $background ?>">
        <?= $this->render('_site-notes') ?>
        <?php
        $header = $this->render('_content-header');
        if ($header) {
            ?>
            <section class="content-header">
                <div class="container-fluid">
                    <?= $header ?>
                </div>
            </section>
            <?php
        }
        $breadcrumbs = $this->render('_content-breadcrumbs');
        if ($breadcrumbs) {
            ?>
            <section class="content-breadcrumbs">
                <div class="container-fluid">
                    <?= $breadcrumbs ?>
                </div>
            </section>
            <?php
        }
        ?>
        <section class="content">
            <?= Alert::widget() ?>
            <?= $content ?>
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
