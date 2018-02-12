<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ComponentType $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Component Type') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Component Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="component-type-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
