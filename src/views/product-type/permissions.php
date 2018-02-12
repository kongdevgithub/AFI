<?php

use app\models\ProductType;
use app\widgets\JavaScript;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ProductTypeSearch $searchModel
 */

$this->title = Yii::t('app', 'Product Type Permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['product-type/index']];
$this->params['breadcrumbs'][] = $this->title;

$productTypes = ProductType::find()
    ->notDeleted()
    ->orderBy(['name' => SORT_ASC])
    ->all();

$roles = ['custom-quoter', 'csr', 'client'];

$auth = Yii::$app->authManager;
?>

<div class="product-type-permissions">

    <?php $form = ActiveForm::begin(); ?>

    <div class="box box-default">
        <div class="box-body no-padding">
            <table class="table table-condensed table-bordered table-striped">
                <thead>
                <tr>
                    <?php
                    foreach ($roles as $role) {
                        ?>
                        <th class="text-center" style="width:50px"><?= $role ?></th>
                        <?php
                    }
                    ?>
                    <th><?= Yii::t('app', 'Name') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // for each product type
                $output = [];
                foreach ($productTypes as $_productType) {
                    ob_start();
                    ?>
                    <tr>
                        <?php
                        foreach ($roles as $role) {
                            ?>
                            <td class="text-left">
                                <?php
                                $checkboxes = [];
                                foreach (['', '_read'] as $type) {
                                    $permission = '_product-type_' . $_productType->id . $type;
                                    //debug($auth->getChildren($role));
                                    $checked = isset($auth->getChildren($role)[$permission]);
                                    $checkboxes[] = Html::checkbox('ProductType[' . $role . '][' . $permission . ']', $checked, [
                                        'class' => 'product-type' . $type,
                                        //'data-toggle' => 'tooltip',
                                        'title' => $role . ' ' . ($type ? 'read' : 'write') . ': ' . $_productType->getBreadcrumbString(' > '),
                                    ]);
                                }
                                echo implode(' ', $checkboxes);
                                ?>
                            </td>
                            <?php
                        }
                        ?>
                        <td><?= $_productType->getBreadcrumbHtml() ?></td>
                    </tr>
                    <?php
                    $output[$_productType->getBreadcrumbString()] = ob_get_clean();
                }
                ksort($output);
                echo implode('', $output);
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end(); ?>

</div>

<?php JavaScript::begin(); ?>
<script>
    $('.product-type').change(function () {
        var $this = $(this);
        if ($this.prop('checked')) {
            $this.parent().find('.product-type_read').prop('disabled', true).prop('checked', true);
        } else {
            $this.parent().find('.product-type_read').prop('disabled', false);
        }
    }).change();
</script>
<?php JavaScript::end(); ?>


