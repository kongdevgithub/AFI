<?php

use app\components\BulkQuoteHelper;
use app\components\MenuItem;
use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\jobs\BaseJobQuote;
use app\components\quotes\products\BaseProductQuote;
use app\models\ProductType;
use app\models\ProductTypeToItemType;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\bootstrap\Nav;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Product Types');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$items = [];
foreach (ProductTypeToItemType::find()->notDeleted()->all() as $productTypeToItemType) {
    if ($productTypeToItemType->productType->deleted_at) {
        continue;
    }
    $items[$productTypeToItemType->id] = strip_tags($productTypeToItemType->productType->getBreadcrumbHtml(' > ')) . ' > ' . $productTypeToItemType->name;
}
asort($items);
foreach ($items as $k => $v) {
    $items[$k] = Html::a($v, ['/product-type-to-item-type/update', 'id' => $k, 'ru' => ReturnUrl::getToken()]);
}
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Product Types'); ?></h3>
    </div>
    <div class="box-body">
        <?= Html::ul($items, ['encode' => false]) ?>
    </div>
</div>
