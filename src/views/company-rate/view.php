<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/d4b4964a63cc95065fa0ae19074007ee
 *
 * @package default
 */


use app\components\fields\BaseField;
use app\models\ProductType;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\models\CompanyRate $model
 */
$this->title = Yii::t('app', 'Company Rate') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Company Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="company-rate-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('app', 'Company Rate') ?></h3>
        </div>
        <div class="box-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'format' => 'raw',
                        'attribute' => 'company_id',
                        'value' => ($model->getCompany()->one() ?
                            Html::a($model->getCompany()->one()->name, ['//company/view', 'id' => $model->getCompany()->one()->id,])
                            :
                            '<span class="label label-warning">?</span>'),
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'product_type_id',
                        'value' => ($model->getProductType()->one() ?
                            Html::a($model->getProductType()->one()->name, ['//product-type/view', 'id' => $model->getProductType()->one()->id,])
                            :
                            '<span class="label label-warning">?</span>'),
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'item_type_id',
                        'value' => ($model->getItemType()->one() ?
                            Html::a($model->getItemType()->one()->name, ['//item-type/view', 'id' => $model->getItemType()->one()->id,])
                            :
                            '<span class="label label-warning">?</span>'),
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'option_id',
                        'value' => ($model->getOption()->one() ?
                            Html::a($model->getOption()->one()->name, ['//option/view', 'id' => $model->getOption()->one()->id,])
                            :
                            '<span class="label label-warning">?</span>'),
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'component_id',
                        'value' => ($model->getComponent()->one() ?
                            Html::a($model->getComponent()->one()->label, ['//component/view', 'id' => $model->getComponent()->one()->id,])
                            :
                            '<span class="label label-warning">?</span>'),
                    ],
                    'size',
                    'companyRateOptionsHtml:raw',
                    'price',
                ],
            ]); ?>
        </div>
    </div>

</div>
