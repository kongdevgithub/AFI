<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/fccccf4deb34aed738291a9c38e87215
 *
 * @package default
 */


use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var app\models\PackageType $model
 */
$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Package Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-type-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
