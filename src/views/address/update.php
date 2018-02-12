<?php

use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Address $model
 */

$this->title = Yii::t('app', 'Update') . ' ' . Yii::t('app', 'Address') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Addresses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="address-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
