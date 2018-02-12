<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Option $model
 */

$this->title = Yii::t('app', 'Create Option');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="option-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
