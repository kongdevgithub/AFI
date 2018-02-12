<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Postcode $model
 */

$this->title = Yii::t('app', 'Create Postcode');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Postcodes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="postcode-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
