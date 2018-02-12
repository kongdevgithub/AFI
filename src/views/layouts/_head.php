<?php
/**
 * @var $this \yii\web\View
 */
use kartik\date\DatePickerAsset;
use kartik\icons\Icon;
use kartik\select2\Select2Asset;
use kartik\select2\ThemeKrajeeAsset;
use newerton\fancybox\FancyBox;
use yii\grid\GridViewAsset;
use yii\helpers\Html;
use yii\helpers\Url;

$ajax = Yii::$app->request->isAjax || Yii::$app->request->get('ajax');

if (!$ajax) {
    ?>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php /* <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> */ ?>
    <?= Html::csrfMetaTags() ?>
    <?php //echo Html::tag('base', null, ['href' => Url::base('https')]) ?>
    <?php
}
?>
<title><?= Html::encode($this->title) ?></title>

<?php
echo FancyBox::widget([
    'target' => 'a[data-fancybox]',
    'config' => [
        'openOpacity' => true,
        'openEffect' => 'elastic',
        'closeEffect' => 'elastic',
        //'prevEffect' => 'elastic',
        //'nextEffect' => 'elastic',
    ],
]);

Icon::map($this, Icon::FA);
//Icon::map($this, Icon::EL);
//Icon::map($this, Icon::TYP);
Icon::map($this, Icon::WHHG);
//Icon::map($this, Icon::OCT);

Select2Asset::register($this);
ThemeKrajeeAsset::register($this);
DatePickerAsset::register($this);
GridViewAsset::register($this);
?>

<?= $this->render('_favicon') ?>

<?php $this->head() ?>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
