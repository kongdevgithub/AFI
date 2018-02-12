<?php

use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\search\ProductSearch;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\Venue;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


$model = new ProductSearch();
$dataProvider = $model->search(Yii::$app->request->get());

?>
<div class="box box-default collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('goldoc', 'Filters') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">

        <?php $form = ActiveForm::begin([
            'method' => 'get',
        ]); ?>

        <?php echo $form->field($model, 'supplier_id')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'supplier_id',
            'data' => ArrayHelper::map(Supplier::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
            'options' => ['multiple' => true],
        ]) ?>

        <?php echo $form->field($model, 'venue_id')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'venue_id',
            'data' => ArrayHelper::map(Venue::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
            'options' => ['multiple' => true],
        ]) ?>

        <?php echo $form->field($model, 'loc') ?>

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
        <?php echo $form->field($model, 'goldoc_manager_id')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'goldoc_manager_id',
            'data' => ArrayHelper::map(User::find()
                ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc')])
                ->orderBy('username')->all(), 'id', 'label'),
            'options' => ['multiple' => true],
        ]) ?>
        <?php echo $form->field($model, 'active_manager_id')->widget(Select2::className(), [
            'model' => $model,
            'attribute' => 'active_manager_id',
            'data' => ArrayHelper::map(User::find()
                ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-active')])
                ->orderBy('username')->all(), 'id', 'label'),
            'options' => ['multiple' => true],
        ]) ?>
        <?php echo Html::tag('div', implode('', [
            Html::label(Yii::t('app', 'Sort'), 'sort'),
            Html::dropDownList('sort', Yii::$app->request->get('sort'), [
                'id' => 'ID - ASC',
                '-id' => 'ID - DESC',
                'venue_id' => 'Venue - ASC',
                '-venue_id' => 'Venue - DESC',
                'loc' => 'LOC - ASC',
                '-loc' => 'LOC - DESC',
                'item_id' => 'Item - ASC',
                '-item_id' => 'Item - DESC',
            ], ['class' => 'form-control']),
        ]), ['class' => 'form-group']); ?>

        <?php echo Html::submitButton(Yii::t('goldoc', 'Filter'), ['class' => 'btn btn-primary']) ?>

        <?php ActiveForm::end(); ?>

    </div>
</div>

