<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Note $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Note') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Notes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="note-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
