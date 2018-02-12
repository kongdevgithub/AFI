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
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="job-status">

    <?= $this->render('_status', ['model' => $model]) ?>

</div>

