<?php

use app\models\Product;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Item $item
 */

?>
<div class="box box-primary box-solid no-margin">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $item->name; ?></h3>
        <div class="box-tools pull-right">
            <?= Html::a('<i class="fa fa-arrows sortable-handle"></i>', null, [
                'class' => 'btn btn-box-tool',
            ]) ?>
        </div>
    </div>
    <div class="box-body">
        <?= $item->getDescription() ?>
    </div>
</div>