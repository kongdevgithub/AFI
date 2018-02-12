<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductToOption $model
 */

$this->title = Yii::t('app', 'Create Product To Option');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product To Options'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-to-option-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
