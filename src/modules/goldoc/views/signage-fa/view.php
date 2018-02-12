<?php

use yii\widgets\DetailView;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\SignageFa $model
 */
$this->title = Yii::t('goldoc', 'Signage FA') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Signage FAs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="signage-fa-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Signage FA') ?></h3>
        </div>
        <div class="box-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'code',
                    'comment',
                    'sign_text:ntext',
                    'goldoc_product_allocated',
                    'material',
                    'width',
                    'height',
                    'fixing',
                    'venueQuantities',
                ],
            ]); ?>
        </div>
    </div>

</div>
