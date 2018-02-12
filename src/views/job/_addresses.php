<?php

use app\components\freight\Freight;
use app\models\Address;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\bootstrap\Alert;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


if (!$model->shippingAddresses) {
    echo Alert::widget([
        'body' => Yii::t('app', 'At least one shipping address is required.'),
        'options' => ['class' => 'alert-warning'],
        'closeButton' => false,
    ]);
}

$createAddressLink = '';
if (Y::user()->can('app_job_shipping-address', ['route' => true])) {
    $createAddressLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add'), [
        'job/shipping-address',
        'id' => $model->id,
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs modal-remote',
    ]);
}

$importAddressLink = '';
if (Y::user()->can('app_job_shipping-address-import', ['route' => true])) {
    $importAddressLink = Html::a('<i class="fa fa-upload"></i> ' . Yii::t('app', 'Import'), [
        'job/shipping-address-import',
        'id' => $model->id,
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
}

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="pull-right"><?= trim($importAddressLink . ' ' . $createAddressLink) ?></div>
        <h3 class="panel-title"><?= Yii::t('app', 'Addresses') ?></h3>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        <table class="table table-condensed no-margin">
            <thead>
            <tr>
                <th width="50%">
                    Billing
                </th>
                <th width="50%">
                    Shipping
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php
                    $items = [];
                    if (Y::user()->can('app_job_billing-address', ['route' => true])) {
                        $items[] = Html::a('<i class="fa fa-pencil"></i>', ['//job/billing-address', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                            'title' => Yii::t('app', 'Update'),
                            'class' => 'modal-remote',
                            'data-toggle' => 'tooltip',
                        ]);
                    }
                    $pieces = [
                        $model->billingAddress->name . ' ' . implode(' ', $items),
                        trim($model->billingAddress->street),
                        trim($model->billingAddress->city . ' ' . $model->billingAddress->postcode . ' ' . $model->billingAddress->state),
                    ];
                    if ($model->billingAddress->country != 'Australia') {
                        $pieces[] = $model->billingAddress->country;
                    }
                    if ($model->billingAddress->contact) {
                        $pieces[] = 'ATTN: ' . trim($model->billingAddress->contact);
                    } else {
                        $pieces[] = 'ATTN: ' . trim($model->contact->label);
                    }
                    if ($model->billingAddress->phone) {
                        $pieces[] = 'PH: ' . trim($model->billingAddress->phone);
                    } else {
                        $pieces[] = 'PH: ' . trim($model->contact->phone);
                    }
                    if ($model->billingAddress->instructions) {
                        $pieces[] = trim($model->billingAddress->instructions);
                    }
                    echo implode('<br>', $pieces);
                    ?>
                </td>
                <td>
                    <?php
                    $shippingAddressCount = count($model->shippingAddresses);

                    if ($shippingAddressCount > 1) {
                        echo '<p>' . Html::button('show', ['class' => 'btn btn-xs btn-default address-grid-toggle']) . '</p>';
                        echo Html::tag('div', 'Multiple shipping addresses.', ['id' => 'address-grid-multiple', 'class' => 'well well-sm']);
                    }

                    $actionButtons = implode(' ', [
                        Html::a(Yii::t('app', 'Create Package'), ['package/address-create', 'ru' => ReturnUrl::getToken()], [
                            'type' => 'button',
                            'title' => Yii::t('app', 'Create Package'),
                            'class' => 'btn btn-default btn-xs modal-remote-form',
                            'data-grid' => 'address-grid',
                        ]),
                    ]);
                    $checkAll = Html::label(Html::checkbox('selection_all', false, ['class' => 'select-on-check-all']) . ' ' . Yii::t('app', 'check all'));
                    $checkAll = Html::tag('div', $checkAll, ['class' => 'checkbox']);
                    $panelAfter = Html::tag('div', $checkAll, ['class' => 'pull-left']) . Html::tag('div', $actionButtons, ['class' => 'pull-right']);
                    $panelAfter = Html::tag('div', $panelAfter, ['class' => 'clearfix']);
                    $dataProvider = new ActiveDataProvider([
                        'query' => $model->getShippingAddresses(),
                        'pagination' => ['pageSize' => 1000],
                        'sort' => false,
                    ]);
                    echo GridView::widget([
                        'id' => 'address-grid',
                        'options' => [
                            'style' => $shippingAddressCount > 1 ? 'display:none;' : '',
                        ],
                        'dataProvider' => $dataProvider,
                        'layout' => '{items}',
                        'columns' => [
                            [
                                'class' => 'kartik\grid\CheckboxColumn'
                            ],
                            [
                                'label' => 'action',
                                'value' => function ($model) {
                                    /** @var Address $model */
                                    $items = [];
                                    if (Y::user()->can('app_job_shipping-address', ['route' => true])) {
                                        $items[] = Html::a('<i class="fa fa-pencil"></i>', ['//job/shipping-address', 'id' => $model->model_id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                            'title' => Yii::t('app', 'Update'),
                                            'class' => 'modal-remote',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    }
                                    if (Y::user()->can('app_job_shipping-address-delete', ['route' => true])) {
                                        $items[] = Html::a('<i class="fa fa-trash"></i>', ['//job/shipping-address-delete', 'id' => $model->model_id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                            'title' => Yii::t('app', 'Delete'),
                                            'data-confirm' => Yii::t('app', 'Are you sure?'),
                                            'data-method' => 'post',
                                            'data-toggle' => 'tooltip',
                                            //'data-pjax' => 0,
                                        ]);
                                    }
                                    return implode(' ', $items);
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'label',
                                'value' => function ($model) use ($shippingAddressCount) {
                                    /** @var Address $model */
                                    if ($shippingAddressCount > 1) {
                                        return $model->name;
                                    }
                                    return $model->getLabel('<br>');
                                },
                                'format' => 'raw',
                            ]
                        ],
                        'panel' => [
                            'heading' => false,
                            'footer' => false,
                            'after' => $panelAfter,
                            'before' => false,
                            'type' => GridView::TYPE_DEFAULT,
                        ],
                        'showHeader' => false,
                        'striped' => false,
                        'bordered' => false,
                    ]);
                    if ($dataProvider->totalCount) {
                        $this->registerJs("jQuery('#address-grid').yiiGridView('setSelectionColumn', " . Json::encode([
                                'name' => 'selection[]',
                                'multiple' => true,
                                'checkAll' => 'selection_all',
                                'class' => 'kv-row-checkbox',
                            ]) . ");");
                    }

                    if ($shippingAddressCount > 1) {
                        JavaScript::begin();
                        ?>
                        <script>
                            $('.address-grid-toggle').click(function () {
                                var $grid = $('#address-grid');
                                var $gridMultiple = $('#address-grid-multiple');
                                if ($grid.is(':visible')) {
                                    $(this).html('show');
                                    $grid.hide();
                                    $gridMultiple.show();
                                } else {
                                    $(this).html('hide');
                                    $grid.show();
                                    $gridMultiple.hide();
                                }
                            });
                        </script>
                        <?php
                        JavaScript::end();
                    }

                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php
        $freight = [];
        $carriers = Freight::getCarrierNames();
        if ($model->freight_method)
            if ($carriers && isset($carriers[$model->freight_method]))
                $freight[] = $carriers[$model->freight_method];
            else
                $freight[] = $model->freight_method;
        if ($model->freight_notes)
            $freight[] = $model->freight_notes;
        echo Yii::t('app', 'Freight') . ': ' . implode(' - ', $freight);
        if (Y::user()->can('app_job_freight', ['route' => true])) {
            echo ' ' . Html::a('<span class="fa fa-pencil"></span>', ['/job/freight', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']);
        }
        if ($model->freight_quote_requested_at && !$model->freight_quote_provided_at) {
            echo Html::tag('span', '', ['class' => 'fa fa-exclamation-triangle', 'title' => Yii::t('app', 'Freight Quote Requested')]);
        }
        ?>
    </div>
</div>
