<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/d4b4964a63cc95065fa0ae19074007ee
 *
 * @package default
 */


use app\components\fields\BaseField;
use app\models\ProductType;
use app\modules\goldoc\models\search\ProductSearch;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Venue $model
 */
$this->title = Yii::t('goldoc', 'Venue') . ': ' . $model->name;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Venues'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="venue-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Venue') ?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?php echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'code',
                            'name',
                        ],
                    ]); ?>
                </div>
                <div class="col-md-6">
                    <?php
                    //if (Yii::$app->user->can('_goldoc_view_prices')) {
                    //    $total = (new Query())
                    //        ->select([
                    //            'SUM(quantity) as quantity_sum',
                    //            'SUM(product_price) as product_price_sum',
                    //            'SUM(labour_price) as labour_price_sum',
                    //            'SUM(machine_price) as machine_price_sum',
                    //            'SUM(total_price) as total_price_sum',
                    //        ])
                    //        ->from('product')
                    //        ->andWhere(['venue_id' => $model->id])
                    //        ->one(Yii::$app->dbGoldoc);
                    //    echo DetailView::widget([
                    //        'model' => $model,
                    //        'attributes' => [
                    //            [
                    //                'label' => Yii::t('goldoc', 'Quantity'),
                    //                'value' => $total['quantity_sum'],
                    //            ],
                    //            [
                    //                'label' => Yii::t('goldoc', 'Product Price'),
                    //                'value' => number_format($total['product_price_sum'], 2),
                    //            ],
                    //            [
                    //                'label' => Yii::t('goldoc', 'Labour Price'),
                    //                'value' => number_format($total['labour_price_sum'], 2),
                    //            ],
                    //            [
                    //                'label' => Yii::t('goldoc', 'Machine Price'),
                    //                'value' => number_format($total['machine_price_sum'], 2),
                    //            ],
                    //            [
                    //                'label' => Yii::t('goldoc', 'Total Price'),
                    //                'value' => number_format($total['total_price_sum'], 2),
                    //            ],
                    //        ],
                    //    ]);
                    //}
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    //$searchModel = new ProductSearch;
    //$searchParams = Yii::$app->request->get();
    //$searchParams['ProductSearch']['venue_id'] = $model->id;
    //$dataProvider = $searchModel->search($searchParams);
    //echo $this->render('/product/_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    ?>

</div>
