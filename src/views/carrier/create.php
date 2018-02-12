<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Carrier $model
 */

$this->title = Yii::t('app', 'Create Carrier');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Carriers'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="carrier-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
