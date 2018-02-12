<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/eeda5c365686c9888dbc13dbc58f89a1
 *
 * @package default
 */


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\search\SignageFaSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="signage-fa-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="signage-fa-searchModalLabel" aria-hidden="true">

    <?php $form = ActiveForm::begin([
		'action' => ['index'],
		'layout' => 'horizontal',
		'method' => 'get',
		'fieldConfig' => [
			'horizontalCssClasses' => [
				'offset' => 'col-sm-offset-3',
				'label' => 'col-sm-3',
				'wrapper' => 'col-sm-9',
				'error' => '',
				'hint' => '',
			],
		],
	]); ?>

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="signage-fa-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?php echo Yii::t('goldoc', 'Search') . ' ' . Yii::t('goldoc', 'SignageFas') ?>                </h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($model, 'id') ?>
                <?php echo $form->field($model, 'code') ?>
                <?php echo $form->field($model, 'comment') ?>
                <?php echo $form->field($model, 'sign_text') ?>
                <?php echo $form->field($model, 'goldoc_product_allocated') ?>
                <?php echo $form->field($model, 'material') ?>
                <?php echo $form->field($model, 'width') ?>
                <?php echo $form->field($model, 'height') ?>
                <?php echo $form->field($model, 'fixing') ?>
            </div>
            <div class="modal-footer">
                <?php echo Html::submitButton(Yii::t('goldoc', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
