<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ItemType $model
 */

$this->title = Yii::t('app', 'Create Item Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Item Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
