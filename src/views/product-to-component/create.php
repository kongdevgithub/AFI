<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductToComponent $model
 */

$this->title = Yii::t('app', 'Create Product To Component');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product To Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-to-component-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
