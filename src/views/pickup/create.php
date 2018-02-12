<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 */

$this->title = Yii::t('app', 'Create Pickup');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pickups'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pickup-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
