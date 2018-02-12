<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\Product $modelCopy
 */

$this->title = $modelCopy->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $modelCopy->job->vid . ': ' . $modelCopy->job->name, 'url' => ['/job/view', 'id' => $modelCopy->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $modelCopy->id . ': ' . $modelCopy->name, 'url' => ['/product/view', 'id' => $modelCopy->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Copy');
?>
<div class="product-copy">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
