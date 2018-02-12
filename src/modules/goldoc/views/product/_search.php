<?php

use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Installer;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Sponsor;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\Type;
use app\modules\goldoc\models\Venue;
use kartik\select2\Select2;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\search\ProductSearch $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div id="product-searchModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="product-searchModalLabel" aria-hidden="true">

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
                <h4 class="modal-title" id="product-searchModalLabel">
                    <i class="fa fa-search"></i>
                    <?php echo Yii::t('goldoc', 'Search') . ' ' . Yii::t('goldoc', 'Products') ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($model, 'id') ?>
                <?php echo $form->field($model, 'status')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'status',
                    'data' => WorkflowHelper::getAllStatusListData($model->getWorkflow()->getId(), $model->getWorkflowSource()),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'venue_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'venue_id',
                    'data' => ArrayHelper::map(Venue::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'loc') ?>

                <?php echo $form->field($model, 'type_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'type_id',
                    'data' => ArrayHelper::map(Type::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'item_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'item_id',
                    'data' => ArrayHelper::map(Item::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'colour_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'colour_id',
                    'data' => ArrayHelper::map(Colour::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'design_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'design_id',
                    'data' => ArrayHelper::map(Design::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'substrate_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'substrate_id',
                    'data' => ArrayHelper::map(Substrate::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'width') ?>
                <?php echo $form->field($model, 'height') ?>
                <?php echo $form->field($model, 'depth') ?>
                <?php echo $form->field($model, 'quantity') ?>
                <?php echo $form->field($model, 'goldoc_manager_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'goldoc_manager_id',
                    'data' => ArrayHelper::map(User::find()
                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc')])
                        ->orWhere(['id' => $model->goldoc_manager_id])
                        ->orderBy('username')->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'active_manager_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'active_manager_id',
                    'data' => ArrayHelper::map(User::find()
                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-active')])
                        ->orWhere(['id' => $model->active_manager_id])
                        ->orderBy('username')->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'supplier_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'supplier_id',
                    'data' => ArrayHelper::map(Supplier::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'sponsor_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'sponsor_id',
                    'data' => ArrayHelper::map(Sponsor::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>
                <?php echo $form->field($model, 'installer_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'installer_id',
                    'data' => ArrayHelper::map(Installer::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                    'options' => ['multiple' => true],
                ]) ?>

                <?php echo $form->field($model, 'product_unit_price') ?>
                <?php echo $form->field($model, 'product_price') ?>
                <?php echo $form->field($model, 'labour_price') ?>
                <?php echo $form->field($model, 'machine_price') ?>
                <?php echo $form->field($model, 'total_price') ?>

            </div>
            <div class="modal-footer">
                <?php echo Html::submitButton(Yii::t('goldoc', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
