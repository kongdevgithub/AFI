<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Note $model
 */

$this->title = Yii::t('app', 'Create Note');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Notes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="note-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
