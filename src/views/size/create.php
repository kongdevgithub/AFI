<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Size $model
 */

$this->title = Yii::t('app', 'Create Size');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Sizes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="size-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
