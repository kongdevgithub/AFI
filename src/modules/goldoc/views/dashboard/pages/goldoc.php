<?php

use app\components\ReturnUrl;
use app\modules\goldoc\components\MenuItem;
use app\modules\goldoc\models\search\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('goldoc', 'GOLDOC Dashboard');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

?>
<div class="dashboard-goldoc">

    <?= $this->render('_filters') ?>

    <div class="row">
        <div class="col-md-4">
            <?php
            if (Yii::$app->user->can('goldoc_product_create', ['route' => true])) {
                echo Html::a('<i class="fa fa-plus"></i> ' . Yii::t('goldoc', 'Request Product'), [
                    'product/create',
                    'ru' => ReturnUrl::getToken()
                ], ['class' => 'btn btn-primary btn-xxl btn-block']);
            }
            ?>
            <br>

            <?php
            // draft
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/draft',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Draft'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // budget approval
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/budgetApproval',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Budget Approval'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // artwork approval
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/artworkApproval',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Artwork Approval'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // artwork upload
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/artworkUpload',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Artwork Upload'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
    </div>

</div>