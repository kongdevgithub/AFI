<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\PackageForm $model
 */

$this->title = Yii::t('app', 'Create Package');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
