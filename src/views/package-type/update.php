<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/fcd70a9bfdf8de75128d795dfc948a74
 *
 * @package default
 */


use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var app\models\PackageType $model
 */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Package Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class=" package-type-update">

    <?= $this->render('_menu', compact('model')); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
