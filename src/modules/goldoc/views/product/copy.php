<?php

use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Product $modelCopy
 * @var app\modules\goldoc\models\Product $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . $modelCopy->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $modelCopy->id, 'url' => ['view', 'id' => $modelCopy->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Copy');
?>
<div class="product-copy">

    <?php echo $this->render('_menu', ['model' => $modelCopy]); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
