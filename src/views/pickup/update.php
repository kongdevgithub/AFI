<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Pickup') . ' ' . $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pickups'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="pickup-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
