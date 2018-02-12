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
 * @var app\modules\goldoc\models\search\VenueSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="venue-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="venue-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="venue-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?php echo Yii::t('goldoc', 'Search') . ' ' . Yii::t('goldoc', 'Venues') ?>                </h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($model, 'id') ?>
                <?php echo $form->field($model, 'code') ?>
                <?php echo $form->field($model, 'name') ?>
            </div>
            <div class="modal-footer">
                <?php echo Html::submitButton(Yii::t('goldoc', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
