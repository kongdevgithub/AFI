<?php
/**
 * /app/src/../runtime/giiant/fcd70a9bfdf8de75128d795dfc948a74
 *
 * @package default
 */


use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var app\models\Carrier $model
 */
$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('models', 'Carrier'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => (string)$model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('cruds', 'Edit');
?>
<div class="carrier-update">

    <?php //echo $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
