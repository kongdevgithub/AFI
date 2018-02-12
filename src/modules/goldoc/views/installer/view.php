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
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Installer $model
 */

$this->title = Yii::t('goldoc', 'Installer') . ': ' . $model->name;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Installers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="installer-view">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Installer') ?></h3>
        </div>
        <div class="box-body">
            <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                        'code',
            'name',
            ],
            ]); ?>
        </div>
    </div>

</div>
