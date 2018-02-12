<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
use dektrium\user\models\User;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use zhuravljov\widgets\DatePicker;

/**
 * @var yii\web\View $this
 * @var app\models\form\UnitStatusForm $model
 * @var ActiveForm $form
 */

$this->title = 'item-' . $model->unit->item->id . ': ' . $model->unit->item->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->unit->item->product->job->id . ': ' . $model->unit->item->product->job->name, 'url' => ['/job/view', 'id' => $model->unit->item->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->unit->item->product->id . ': ' . $model->unit->item->product->name, 'url' => ['/product/view', 'id' => $model->unit->item->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->unit->item->id . ': ' . $model->unit->item->name, 'url' => ['/item/view', 'id' => $model->unit->item->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Unit Status');
?>

<div class="item-status">

    <?= $this->render('_status', ['model' => $model]) ?>

</div>

