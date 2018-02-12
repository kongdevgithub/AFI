<?php
/**
 * @var $this \yii\web\View
 * @var string $skin
 */
use app\components\MenuItem;
use app\widgets\Nav;
use cornernote\shortcuts\Y;
use yii\helpers\Html;
use yii\helpers\Url;

// header for other modules
if (Yii::$app->controller->module->id == 'client') {
    echo $this->render('@client/views/layouts/_navbar', ['skin' => $skin]);
    return;
} elseif (Yii::$app->controller->module->id == 'goldoc' || (Yii::$app->user->isGuest && in_array(Yii::$app->request->hostName, ['zamboni.afi.ink', 'zamboni.test.brett.dev']))) {
    echo $this->render('@goldoc/views/layouts/_navbar', ['skin' => $skin]);
    return;
}

$logo = ($skin == 'skin-black') ? 'logo.png' : 'logo-white.png';
?>
<div class="navbar-header">
    <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand"><?= Html::img(Yii::$app->params['s3BucketUrl'] . '/img/' . $logo, ['height' => '40px']) ?></a>
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
        <i class="fa fa-bars"></i>
    </button>
</div>
<?php if (Yii::$app->user->can('staff')) { ?>
    <div id="navbar-collapse" class="collapse navbar-collapse pull-left">
        <?php
        echo Nav::widget([
            'items' => MenuItem::getNavItems(),
            'options' => ['class' => 'navbar-nav'],
            'encodeLabels' => false,
        ]);
        if (Y::user()->can('app_site_search', ['route' => true])) {
            echo Html::beginForm(Url::to(['//site/search']), 'get', ['class' => 'navbar-form navbar-left', 'role' => 'search']);
            echo Html::tag('div', Html::input('text', 'keywords', Y::GET('keywords'), [
                'id' => 'navbar-search-input',
                'class' => 'form-control',
                'placeholder' => Yii::t('app', 'Search'),
            ]), [
                'class' => 'form-group',
            ]);
            echo Html::endForm();
        }
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
        <strong>AFI Branding</strong><span class="hidden-md hidden-sm">: the leader in fabric signage innovation for the retail, exhibition and event sectors.</span>
    </h3>
<?php } ?>
