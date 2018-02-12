<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ComponentType $model
 */

$this->title = Yii::t('app', 'Create Component Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Component Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="component-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
