<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\Correction;
use app\models\form\ProductForm;
use app\models\Item;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use app\models\User;
use app\widgets\JavaScript;
use cornernote\shortcuts\Y;
use kartik\select2\Select2Asset;
use kartik\select2\ThemeKrajeeAsset;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\ReturnUrl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var ProductForm $model
 * @var ActiveForm $form
 */

if (isset($_GET['Product']['product_type_id'])) {
    $model->product->product_type_id = $_GET['Product']['product_type_id'];
}

$productToOption_k = Y::GET('productToOption_k', 0);
$productToComponent_k = Y::GET('productToComponent_k', 0);

$changedAlertEmails = $model->product->getChangedAlertEmails();
if ($changedAlertEmails) {
    $users = [];
    foreach ($changedAlertEmails as $email) {
        $users[] = User::findOne(['email' => $email]);
    }
    echo Alert::widget([
        'body' => '<p>' . Yii::t('app', 'This item is in a critical stage of production.  Please consider advising the following people of your changes:') . '</p>'
            . Html::ul(ArrayHelper::map($users, 'id', 'label')),
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

$select2Options = [
    'multiple' => false,
    'theme' => 'krajee',
    'placeholder' => '',
    'language' => 'en-US',
    'width' => '100%',
    'allowClear' => true,
    'minimumInputLength' => 1,
    'ajax' => [
        'url' => Url::to(['component/json-list']),
        'dataType' => 'json',
        'data' => new JsExpression('function(params) { return {q:params.term}; }')
    ],
];

?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Product',
        'type' => 'horizontal',
        'enableClientValidation' => false,
        'formConfig' => [
            'labelSpan' => 4,
        ],
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $model->errorSummary($form); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Product'); ?></h3>
                    <div class="box-tools pull-right">
                        <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Option'), 'javascript:void(0);', [
                            'id' => 'product-new-productToOption-button',
                            'class' => 'btn btn-box-tool',
                        ]) ?>
                        <?= Html::a('<i class="fa fa-upload"></i> ' . Yii::t('app', 'Bulk Create'), ['bulk-create', 'Product' => Yii::$app->request->get('Product')], [
                            'class' => 'btn btn-box-tool',
                        ]) ?>
                    </div>
                </div>
                <div class="box-body">

                    <table class="table table-condensed">
                        <tbody>
                        <tr>
                            <td style="border: 0; padding: 0;">
                                <?php if ($model->product->scenario == 'copy') { ?>
                                    <?= $form->field($model->product, 'job_id')->textInput() ?>
                                <?php } ?>
                                <?php if ($changedAlertEmails) { ?>
                                    <?= $form->field($model, 'correction_reason')->dropDownList(Correction::optsReason(), ['prompt' => '']) ?>
                                <?php } ?>
                                <?= $form->field($model->product, 'name')->textInput(['maxlength' => true]) ?>
                                <?= $form->field($model->product, 'details')->textarea() ?>
                                <?= $form->field($model->product, 'quote_hide_item_description')->checkbox() ?>
                                <?php
                                echo Html::activeHiddenInput($model->product, 'quote_class');
                                //echo $form->field($model->product, 'quote_class')->dropDownList(BaseProductQuote::opts(), [
                                //    'prompt' => '',
                                //]);
                                ?>
                                <?= $form->field($model->product, 'prebuild_required')->checkbox() ?>
                                <?= $form->field($model->product, 'quantity')->textInput() ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <?php
                    // productToOption table
                    $productToOption = new ProductToOption();
                    $productToOption->loadDefaultValues();
                    echo '<div id="product-productToOptions" class="table table-condensed">';
                    // existing productToOption
                    foreach ($model->productToOptions as $key => $_productToOption) {
                        $display = false;
                        if ($_productToOption->productTypeToOption) {
                            if (!$_productToOption->productTypeToOption->product_type_to_item_type_id) {
                                $display = true;
                            }
                        } else {
                            if (!$_productToOption->item_id) {
                                $display = true;
                            }
                        }
                        if ($display) {
                            if ($_productToOption->isNewRecord) {
                                $key = strpos($key, 'new') !== false ? $key : 'new' . ($key + $productToOption_k);
                            } else {
                                $key = $_productToOption->id;
                            }
                            echo '<div class="table-row">';
                            echo $this->render('_form-product-to-option', [
                                'key' => $key,
                                'itemKey' => false,
                                'form' => $form,
                                'productToOption' => $_productToOption,
                                'allowOptionChange' => false,
                                'allowOptionRemove' => false,
                            ]);
                            echo '</div>';
                        }
                    }
                    $productToOption_k += 1000;
                    // new productToOption
                    echo '<div class="table-row" id="product-new-productToOption-block" style="display: none;">';
                    echo $this->render('_form-product-to-option', [
                        'key' => '__productToOption_id__',
                        'itemKey' => false,
                        'form' => $form,
                        'productToOption' => $productToOption,
                        'allowOptionChange' => true,
                        'allowOptionRemove' => true,
                    ]);
                    echo '</div>';
                    echo '</div>';
                    ?>
                    <?php JavaScript::begin(['position' => View::POS_HEAD]) ?>
                    <script>
                        var productToOption_k = <?= $productToOption_k ?>;
                    </script>
                    <?php JavaScript::end() ?>
                    <?php JavaScript::begin() ?>
                    <script>
                        // add productToOption button
                        $('#product-new-productToOption-button').on('click', function () {
                            productToOption_k += 1;
                            $('#product-productToOptions')
                                .append('<div class="table-row">' + $('#product-new-productToOption-block').html().replace(/__productToOption_id__/g, 'new' + productToOption_k) + '</div>');
                        });
                        // remove productToOption button
                        $(document).on('click', '.product-remove-productToOption-button', function () {
                            $(this).closest('.table-row').remove();
                        });
                        // change productToOption option_id
                        $(document).on('change', '.ProductToOption_option_id', function () {
                            var $this = $(this);
                            $this.closest('.table-row').load('<?= Url::to(['/product/product-to-option-fields']) ?>', {
                                option_id: $this.val(),
                                key: $this.attr('data-key'),
                                itemKey: $this.attr('data-item-key'),
                                _csrf: yii.getCsrfToken()
                            });
                        });
                    </script>
                    <?php JavaScript::end() ?>

                    <?php
                    // productToComponent table
                    $productToComponent = new ProductToComponent();
                    $productToComponent->loadDefaultValues();
                    echo '<div id="product-productToComponents" class="table table-condensed">';
                    // existing productToComponent
                    foreach ($model->productToComponents as $key => $_productToComponent) {
                        $display = false;
                        if ($_productToComponent->productTypeToComponent) {
                            if (!$_productToComponent->productTypeToComponent->product_type_to_item_type_id) {
                                $display = true;
                            }
                        } else {
                            if (!$_productToComponent->item_id) {
                                $display = true;
                            }
                        }
                        if ($display) {
                            if ($_productToComponent->isNewRecord) {
                                $key = strpos($key, 'new') !== false ? $key : 'new' . ($key + $productToComponent_k);
                            } else {
                                $key = $_productToComponent->id;
                            }
                            echo '<div class="table-row">';
                            echo $this->render('_form-product-to-component', [
                                'key' => $_productToComponent->isNewRecord ? (strpos($key, 'new') !== false ? $key : 'new' . $key) : $_productToComponent->id,
                                'itemKey' => false,
                                'form' => $form,
                                'productToComponent' => $_productToComponent,
                                'allowComponentChange' => false,
                                'allowComponentRemove' => false,
                            ]);
                            echo '</div>';
                        }
                    }
                    $productToComponent_k += 1000;
                    //// new productToComponent
                    //echo '<div class="table-row" id="product-new-productToComponent-block" style="display: none;">';
                    //echo $this->render('_form-product-to-component', [
                    //    'key' => '__productToComponent_id__',
                    //    'itemKey' => false,
                    //    'form' => $form,
                    //    'productToComponent' => $productToComponent,
                    //    'allowComponentChange' => true,
                    //    'allowComponentRemove' => true,
                    //]);
                    //echo '</div>';
                    echo '</div>';
                    ?>
                    <?php JavaScript::begin(['position' => View::POS_HEAD]) ?>
                    <script>
                        var productToComponent_k = <?= $productToComponent_k ?>;
                    </script>
                    <?php JavaScript::end() ?>
                    <?php JavaScript::begin() ?>
                    <script>
                        // add productToComponent button
                        $('#product-new-productToComponent-button').on('click', function () {
                            productToComponent_k += 1;
                            $('#product-productToComponents')
                                .append('<div class="table-row">' + $('#product-new-productToComponent-block').html().replace(/__productToComponent_id__/g, 'new' + productToComponent_k) + '</div>');
                            $('.product-productToComponents').find('select:visible').select2(<?= Json::encode($select2Options) ?>);
                        });
                        // remove productToComponent button
                        $(document).on('click', '.product-remove-productToComponent-button', function () {
                            $(this).closest('.table-row').remove();
                        });
                        // select2 on component
                        $('.product-productToComponents').find('select:visible').select2(<?= Json::encode($select2Options) ?>);

                    </script>
                    <?php JavaScript::end() ?>

                    <?php
                    unset($key);
                    $this->registerAssetBundle(Select2Asset::className());
                    $this->registerAssetBundle(ThemeKrajeeAsset::className());
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <?= $this->render('/note/_index', ['model' => $model->product->productType, 'showActions' => false]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->render('/attachment/_index', ['model' => $model->product->productType, 'showActions' => false]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Items'); ?></h3>
            <div class="box-tools pull-right">
                <?php if (Yii::$app->user->can('custom-quoter')) { ?>
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Clone Item'), 'javascript:void(0);', [
                        'id' => 'product-clone-item-button',
                        'class' => 'btn btn-box-tool',
                    ]) ?>
                <?php } ?>
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Item'), 'javascript:void(0);', [
                    'id' => 'product-new-item-button',
                    'class' => 'btn btn-box-tool',
                ]) ?>
            </div>
        </div>
        <div class="box-body">

            <?php
            // init item model
            $item = new Item();
            $item->loadDefaultValues();

            // new item
            echo '<div id="product-new-item-block" style="display: none;">';
            echo $this->render('_form-product-item', [
                'key' => '__productItem_id__',
                'form' => $form,
                'model' => $model,
                'item' => $item,
                'productToOption_k' => $productToOption_k,
                'productToComponent_k' => $productToComponent_k,
            ]);
            echo '</div>';
            // clone item
            echo '<div id="product-clone-item-block" style="display: none;">';
            echo $this->render('_form-product-item-clone', [
                'key' => '__productItem_id__',
                'form' => $form,
                'model' => $model,
                'item' => $item,
            ]);
            echo '</div>';
            ?>

            <div id="product-items" class="row row-md-4-clear">
                <?php
                // existing items
                foreach ($model->items as $key => $_item) {
                    if ($_item->isNewRecord && !$_item->id) {
                        $_item->id = $key;
                        $_key = strpos($key, 'new') !== false ? $key : 'new' . $key;
                    } else {
                        $_key = $_item->id;
                    }
                    echo $this->render('_form-product-item', [
                        'key' => !empty($_GET['clone']) ? '__cloneItem_id__' : $_key,
                        'form' => $form,
                        'model' => $model,
                        'item' => $_item,
                        'productToOption_k' => $productToOption_k,
                        'productToComponent_k' => $productToComponent_k,
                    ]);
                }
                ?>

                <?php JavaScript::begin(['runOnAjax' => false]) ?>
                <script>
                    // add item button
                    var item_k = <?php echo isset($key) ? str_replace('new', '', $key) : 0; ?>;
                    $('#product-new-item-button').on('click', function () {
                        item_k += 1;
                        $('#product-items').append($('#product-new-item-block').html().replace(/__productItem_id__/g, 'new' + item_k));

                        // add productToOption button
                        $('#product-new-productToOption-button-new' + item_k).on('click', function () {
                            productToOption_k += 1;
                            var _item_k = $(this).attr('data-key');
                            //console.log(_item_k);
                            $('#product-productToOptions-' + _item_k)
                                .append('<div class="table-row">' + $('#product-new-productToOption-block-' + _item_k).html().replace(/__productToOption_id__/g, 'new' + productToOption_k) + '</div>');
                        });
                        // add productToComponent button
                        $('#product-new-productToComponent-button-new' + item_k).on('click', function () {
                            productToComponent_k += 1;
                            var _item_k = $(this).attr('data-key');
                            var $productToComponents = $('#product-productToComponents-' + _item_k);
                            $productToComponents.append('<div class="table-row">' + $('#product-new-productToComponent-block-' + _item_k).html().replace(/__productToComponent_id__/g, 'new' + productToComponent_k) + '</div>');
                            $productToComponents.find('select:visible').select2(<?= Json::encode($select2Options) ?>);
                            $productToComponents.find('#ProductToComponents_new' + productToComponent_k + '_quote_class').val(<?= Json::encode(BaseComponentQuote::className()) ?>);
                        });
                    });
                    <?php if (Yii::$app->user->can('custom-quoter')) { ?>
                    // clone item button
                    $('#product-clone-item-button').on('click', function () {
                        item_k += 1;
                        $('#product-items').append($('#product-clone-item-block').html().replace(/__productItem_id__/g, 'new' + item_k));
                    });
                    <?php } ?>
                    // remove item button
                    $(document).on('click', '.product-remove-item-button', function () {
                        $(this).closest('.product-item').remove();
                    });
                </script>
                <?php JavaScript::end() ?>

                <?php
                unset($key);
                ?>

            </div>

        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->product->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->product->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?php if (!$model->product->isNewRecord) echo Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->product->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>

<?php JavaScript::begin() ?>
<script>
    // change item name
    $(document).on('change', '.item-name', function () {
        $(this).closest('.product-item').find('.box-header h3').html($(this).val());
    });
</script>
<?php JavaScript::end() ?>
