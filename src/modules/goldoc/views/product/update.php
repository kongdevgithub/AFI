<?php

use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Product $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Update');
?>
<div class="product-update">

    <?php echo $this->render('_menu', ['model' => $model]); ?>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
