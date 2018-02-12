<?php

use app\components\freight\Freight;
use app\components\ReturnUrl;
use app\models\Item;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\bootstrap\Alert;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Freight');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()
?>

<div class="job-freight">

    <div class="row">
        <div class="col-md-6">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Job',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'action' => ['freight', 'id' => $model->id],
                'encodeErrorSummary' => false,
                'fieldConfig' => [
                    'errorOptions' => [
                        'encode' => false,
                        'class' => 'help-block',
                    ],
                ],
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($model);

            echo $form->field($model, 'quote_freight_price')->textInput();

            echo $form->field($model, 'freight_method')->dropDownList(Freight::getCarrierNames(), ['prompt' => '']);

            echo $form->field($model, 'freight_notes')->textInput();

            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            echo ' ' . Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']);
            ActiveForm::end();
            ?>
        </div>
        <div class="col-md-6">
            <?php
            $job = $model;
            $boxes = Freight::getBoxes($job);
            $carriers = Freight::getCarrierFreight($job->shippingAddresses && count($job->shippingAddresses) == 1 ? $job->shippingAddresses[0] : false, $boxes);
            $unboxed = Freight::getUnboxed($job, $boxes);

            // quantity forked
            $quantityForked = false;
            foreach ($job->products as $product) {
                if ($product->forkQuantityProducts) {
                    $quantityForked = true;
                    break;
                }
            }
            if ($quantityForked) {
                echo Alert::widget([
                    'body' => Yii::t('app', 'Products contain quantity forks, please discuss quoting between Sales and Despatch.'),
                    'options' => ['class' => 'alert-danger'],
                    'closeButton' => false,
                ]);
            }

            // unboxed
            $items = [];
            foreach ($unboxed as $item_id => $quantity) {
                $item = Item::findOne($item_id);
                $item->quantity = $quantity;
                $items[] = $item;
            }
            if ($items) {
                $columns = [];
                $columns[] = [
                    'label' => Yii::t('app', 'Item'),
                    'value' => function ($model) {
                        /** @var Item $model */
                        return Html::a('item-' . $model->id, ['/item/view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ];
                $columns[] = [
                    'label' => Yii::t('app', 'Name'),
                    'value' => function ($model) {
                        /** @var Item $model */
                        $size = $model->getSizeHtml();
                        return $model->product->name . ' | ' . $model->name . ($size ? ' | ' . $size : '');
                    },
                    'format' => 'raw',
                ];
                $columns[] = [
                    'label' => Yii::t('app', 'Quantity'),
                    'value' => function ($model) {
                        /** @var Item $model */
                        return $model->quantity;
                    },
                    'format' => 'raw',
                ];
                echo GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $items,
                        'pagination' => ['pageSize' => 0],
                        'sort' => false,
                    ]),
                    'layout' => '{items}',
                    'columns' => $columns,
                    'panel' => [
                        'heading' => Yii::t('app', 'Items with No Freight Quoted'),
                        'footer' => false,
                        'before' => false,
                        'after' => false,
                        'type' => GridView::TYPE_DEFAULT,
                    ],
                    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]);

            }

            // carriers
            if ($carriers) {
                $columns = [];
                $columns[] = 'name';
                $columns[] = 'zone';
                $columns[] = [
                    'attribute' => 'price',
                    'value' => function ($model) use ($unboxed, $quantityForked, $job) {
                        $quote = ($unboxed && $model['price']) || $model['quote'] || $quantityForked;
                        $text = [];
                        if ($quote) {
                            //if ($model['price'] > 0) {
                            //    $text[] = Html::button('$' . number_format($model['price'], 2), [
                            //        'class' => 'btn btn-default btn-xs',
                            //    ]);
                            //}
                            $text[] = Html::a(Yii::t('app', 'Get a Quote'), ['job/request-freight-quote', 'id' => $job->id, 'freight_method' => $model['method'], 'ru' => ReturnUrl::getRequestToken()], [
                                'class' => 'btn btn-info btn-xs',
                            ]);
                        } else {
                            $text[] = Html::a('$' . number_format($model['price'], 2), ['job/set-freight-quote', 'id' => $job->id, 'freight_method' => $model['method'], 'ru' => ReturnUrl::getRequestToken()], [
                                'class' => 'btn btn-primary btn-xs',
                            ]);
                        }
                        return implode(' ', $text);
                    },
                    'format' => 'raw',
                ];
                echo GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $carriers,
                        'pagination' => ['pageSize' => 0],
                        'sort' => false,
                    ]),
                    'layout' => '{items}',
                    'columns' => $columns,
                    'panel' => [
                        'heading' => Yii::t('app', 'Freight Options'),
                        'footer' => false,
                        'before' => false,
                        'after' => false,
                        'type' => GridView::TYPE_DEFAULT,
                    ],
                    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]);
            }


            ?>
        </div>
    </div>

</div>

