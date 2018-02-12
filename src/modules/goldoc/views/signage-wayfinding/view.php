<?php

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
 * @var app\modules\goldoc\models\SignageWayfinding $model
 */
$this->title = Yii::t('goldoc', 'Signage Wayfinding') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Signage Wayfindings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="signage-wayfinding-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php echo Yii::t('goldoc', 'Signage Wayfinding') ?></h3>
        </div>
        <div class="box-body">
            <?php echo DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'batch',
                    'quantity',
                    'sign_id',
                    'sign_code',
                    'level',
                    'message_side_1:ntext',
                    'message_side_2:ntext',
                    'fixing',
                    'notes',
                ],
            ]); ?>
        </div>
    </div>

</div>
