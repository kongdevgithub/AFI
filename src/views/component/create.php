<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Component $model
 */

$this->title = Yii::t('app', 'Create Component');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="component-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
