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
 * @var app\models\search\CompanyRateSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="company-rate-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="company-rate-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="company-rate-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?php echo Yii::t('app', 'Search') . ' ' . Yii::t('app', 'CompanyRates') ?>                </h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($model, 'id') ?>
                <?php echo $form->field($model, 'company_id') ?>
                <?php echo $form->field($model, 'product_type_id') ?>
                <?php echo $form->field($model, 'item_type_id') ?>
                <?php echo $form->field($model, 'option_id') ?>
                <?php echo $form->field($model, 'component_id') ?>
                <?php echo $form->field($model, 'price') ?>
                <?php echo $form->field($model, 'created_at') ?>
                <?php echo $form->field($model, 'updated_at') ?>
                <?php echo $form->field($model, 'deleted_at') ?>
            </div>
            <div class="modal-footer">
                <?php echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
