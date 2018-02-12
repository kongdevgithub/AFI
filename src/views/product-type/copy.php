<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductType $model
 */

$this->title = Yii::t('app', 'Copy Product Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-type-copy">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
