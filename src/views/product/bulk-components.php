<?php

use app\models\Product;
use app\models\ProductToOption;
use app\widgets\JavaScript;
use kartik\form\ActiveForm;
use app\components\ReturnUrl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductBulkComponentsForm $model
 */


$this->title = $model->job->getTitle();

$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->vid . ': ' . $model->job->name, 'url' => ['/job/view', 'id' => $model->job->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Bulk Components');

$select2options = [
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
<div class="product-bulk-components">

    <div class="box">
        <div class="box-header with-border">
            <?php
            // new product button
            echo Html::a('New Product', 'javascript:void(0);', [
                'class' => 'bulk-components-new-product-button pull-right btn btn-default btn-xs'
            ]);
            ?>
        </div>
        <div class="box-body">

            <?php $form = ActiveForm::begin([
                'id' => 'Address',
                'type' => 'horizontal',
                'enableClientValidation' => false,
            ]); ?>

            <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

            <?= $form->errorSummary($model); ?>

            <?php
            // product table
            echo '<table id="bulk-components-products" class="table table-condensed table-bordered">';
            echo '<thead>';
            echo '<tr>';
            echo '<th width="45%">' . Yii::t('app', 'Component') . '</th>';
            echo '<th width="10%">' . Yii::t('app', 'Product Qty') . '</th>';
            echo '<th width="10%">' . Yii::t('app', 'Component Qty/Length') . '</th>';
            echo '<th width="45%">' . Yii::t('app', 'Details') . '</th>';
            echo '<td>&nbsp;</td>';
            echo '</tr>';
            echo '</thead>';
            echo '</tbody>';

            // existing products fields
            foreach ($model->products as $key => $_product) {
                if (!$_product->isNewRecord) {
                    $key = $_product->id;
                    $_productToOption = $_product->items[0]->productToOptions[0];
                } else {
                    $key = (strpos($key, 'new') !== false ? $key : 'new' . $key);
                    $_productToOption = new ProductToOption();
                    $_productToOption->loadDefaultValues();
                    $_productToOption->product_id = $key;
                }
                echo '<tr>';
                echo $this->render('_bulk-components-product', [
                    'key' => $key,
                    'form' => $form,
                    'product' => $_product,
                    'productToOption' => $_productToOption,
                ]);
                echo '</tr>';
            }

            // new product fields
            $product = new Product();
            $product->loadDefaultValues();
            $productToOption = new ProductToOption();
            $productToOption->loadDefaultValues();
            echo '<tr id="bulk-components-new-product-block" style="display: none;">';
            echo $this->render('_bulk-components-product', [
                'key' => '__id__',
                'form' => $form,
                'product' => $product,
                'productToOption' => $productToOption,
            ]);
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
            ?>

            <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success',
            ]); ?>

            <?php ActiveForm::end(); ?>


        </div>
        <div class="box-footer">
            <?php
            // new product button
            echo Html::a('New Product', 'javascript:void(0);', [
                'class' => 'bulk-components-new-product-button pull-right btn btn-default btn-xs'
            ]);
            ?>
        </div>
    </div>
</div>

<?php JavaScript::begin() ?>
<script>

    // add product button
    var product_k = <?php echo isset($key) ? str_replace('new', '', $key) : 0; ?>;
    $('.bulk-components-new-product-button').on('click', function () {
        product_k += 1;
        $('#bulk-components-products').find('tbody')
            .append('<tr>' + $('#bulk-components-new-product-block').html().replace(/__id__/g, 'new' + product_k) + '</tr>');

        // select2 on new row
        $('#ProductToOptions_new' + product_k + '_valueDecoded_component').select2(<?= Json::encode($select2options) ?>);
    });

    // remove product button
    $(document).on('click', '.bulk-components-remove-product-button', function () {
        $(this).closest('tbody tr').remove();
    });

    // insert new row
    $('#product-new-product-button').click();

    // select2 on existing rows
    $('#bulk-components-products').find('select:visible').select2(<?= Json::encode($select2options) ?>);

</script>
<?php JavaScript::end(); ?>
