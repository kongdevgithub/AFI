<?php

use yii\widgets\DetailView;

/**
 *
 * @var yii\web\View $this
 * @var app\models\PackageType $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Package Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-type-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Package Type Details'); ?></h3>
        </div>
        <div class="box-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'type',
                    'width',
                    'length',
                    'height',
                    'dead_weight',
                ],
            ]); ?>
        </div>
    </div>

</div>
