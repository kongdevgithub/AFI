<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Contact') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="contact-update">

    <?= $this->render('_menu', compact('model')); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
