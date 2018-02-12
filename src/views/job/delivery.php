<?php

use app\models\ItemToAddress;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobDeliveryForm $model
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="job-delivery">

    <?= $this->render('_menu', ['model' => $model->job]); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Delivery'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Item',
                'type' => 'horizontal',
                'formConfig' => ['labelSpan' => 10],
                'enableClientValidation' => false,
            ]);
            echo $model->errorSummary($form);

            ?>
            <div class="table-responsive">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <td colspan="3">
                            Item
                        </td>
                        <td >
                            Assigned / Total
                        </td>
                        <?php
                        foreach ($model->job->shippingAddresses as $shippingAddress) {
                            ?>
                            <td>
                                <?php
                                echo $shippingAddress->name;
                                ?>
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($model->job->products as $product) {
                        $itemCount = count($product->items);
                        ?>
                        <?php
                        foreach ($product->items as $k => $item) {
                            $unassigned = $item->quantity * $product->quantity;
                            $assigned = [];
                            foreach ($model->job->shippingAddresses as $_k => $shippingAddress) {
                                if (isset($_POST['JobDeliveryForm']['quantity'][$item->id][$shippingAddress->id])) {
                                    $addressQuantity = $_POST['JobDeliveryForm']['quantity'][$item->id][$shippingAddress->id];
                                } else {
                                    $itemToAddress = ItemToAddress::findOne([
                                        'item_id' => $item->id,
                                        'address_id' => $shippingAddress->id,
                                    ]);
                                    $addressQuantity = $itemToAddress ? $itemToAddress->quantity : 0;
                                }
                                $assigned[$shippingAddress->id] = $addressQuantity;
                                $unassigned -= $addressQuantity;
                            }
                            ?>
                            <tr>
                                <?php
                                if (!$k) {
                                    ?>
                                    <td rowspan="<?= $itemCount ?>" width="20%">
                                        <?php
                                        echo Html::a($product->name, ['product/view', 'id' => $product->id]);
                                        ?>
                                    </td>
                                    <td rowspan="<?= $itemCount ?>" width="5%">
                                        <?php
                                        echo Html::tag('span', $product->getSizeHtml(), ['class' => 'label label-default']);
                                        ?>
                                    </td>
                                    <?php
                                }
                                ?>
                                <td width="10%">
                                    <?php
                                    echo Html::a($item->name, ['item/view', 'id' => $item->id]);
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $cssClass = 'label-danger';
                                    if ($unassigned == 0) {
                                        $cssClass = 'label-success';
                                    }
                                    echo Html::tag('span', ($item->quantity * $product->quantity - $unassigned) . ' / ' . ($item->quantity * $product->quantity), ['class' => 'label ' . $cssClass]);
                                    ?>
                                </td>
                                <?php
                                foreach ($model->job->shippingAddresses as $shippingAddress) {
                                    ?>
                                    <td class="text-center">
                                        <?php
                                        //echo Html::tag('span', $assigned[$shippingAddress->id], ['class' => 'label label-default']);
                                        echo Html::textInput("JobDeliveryForm[quantity][$item->id][$shippingAddress->id]", $assigned[$shippingAddress->id], ['style' => 'width:40px;text-align:right;']);
                                        ?>
                                    </td>
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <?php
            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>

