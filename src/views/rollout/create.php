<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Rollout $model
 */

$this->title = Yii::t('app', 'Create Rollout');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Rollouts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rollout-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
