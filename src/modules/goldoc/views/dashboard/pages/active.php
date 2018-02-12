<?php

use app\components\ReturnUrl;
use app\modules\goldoc\components\MenuItem;
use app\modules\goldoc\models\search\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('goldoc', 'ACTIVE Dashboard');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

?>
<div class="dashboard-active">

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
            // site check
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/siteCheck',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Site Check'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // not priced
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'supplier_priced' => '0',
                'status' => [
                    'goldoc-product/siteCheck',
                    'goldoc-product/quotePending',
                    'goldoc-product/budgetApproval',
                    'goldoc-product/artworkApproval',
                    'goldoc-product/artworkUpload',
                    'goldoc-product/productionPending',
                    'goldoc-product/production',
                    'goldoc-product/warehouseMelbourne',
                    'goldoc-product/warehouseGoldCoast',
                    'goldoc-product/installation',
                    'goldoc-product/complete',
                ],
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Not Priced'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // quote pending
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/quotePending',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Quote Pending'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // production pending
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/productionPending',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Production Pending'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // production
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/production',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Production'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // warehouse melbourne
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/warehouseMelbourne',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Warehouse Melbourne'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

            <?php
            // warehouse gold coast
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/warehouseGoldCoast',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Warehouse Gold Coast'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // installation
            $params = ArrayHelper::merge(Yii::$app->request->get(), ['ProductSearch' => [
                'status' => 'goldoc-product/installation',
            ]]);
            $productSearch = new ProductSearch;
            $dataProvider = $productSearch->search($params);
            echo $this->render('_products', [
                'params' => $params,
                'title' => Html::a(Yii::t('goldoc', 'Installation'), ['/goldoc/product/index', 'ProductSearch' => $params['ProductSearch']]),
            ]);
            ?>

        </div>
    </div>

</div>