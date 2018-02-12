<?php

use yii\helpers\Html;

/**
* @var yii\web\View $this
* @var app\models\Attachment $model
*/

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Attachment') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Attachments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="attachment-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>