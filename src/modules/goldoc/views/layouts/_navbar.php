<?php
/**
 * @var $this \yii\web\View
 * @var string $skin
 */
use app\modules\goldoc\components\MenuItem;
use app\widgets\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

$logo = ($skin == 'skin-black') ? 'logo.png' : 'logo-white.png';
?>
<div class="navbar-header">
    <a href="<?= Url::to(['//goldoc/default']) ?>" class="navbar-brand"><?= Html::img('https://s3.afi.ink/attachment/Product/2/d69f6da759150675eaca0b42292e5056/logopng.png', ['height' => '40px']) ?></a>
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
        <i class="fa fa-bars"></i>
    </button>
</div>
<?php if (!Yii::$app->user->isGuest) { ?>
    <div id="navbar-collapse" class="collapse navbar-collapse pull-left">
        <?php
        echo Nav::widget([
            'items' => MenuItem::getNavItems(),
            'options' => ['class' => 'navbar-nav'],
            'encodeLabels' => false,
        ]);
        ?>
    </div>
    <div class="navbar-custom-menu">
        <?= Nav::widget([
            'items' => MenuItem::getCustomItems(),
            'options' => ['class' => 'navbar-nav'],
            'encodeLabels' => false,
            'dropDownCaret' => false,
        ]) ?>
    </div>
<?php } else { ?>
    <h3 class="hidden-xs" style="margin: 11px 0 0 70px;">
        <strong>ZAMBONI</strong><span class="hidden-md hidden-sm">: Commonwealth Games 2018 Signage Management</span>
    </h3>
<?php } ?>
