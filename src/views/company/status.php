<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Contact;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\User;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 * @var yii\bootstrap\ActiveForm $form
 */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="company-status">

    <?= $this->render('_status', ['model' => $model]) ?>

</div>
