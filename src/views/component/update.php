<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Component $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Component') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="component-update">

    <?= $this->render('_menu', compact('model')); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
