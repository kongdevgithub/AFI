<?php

use app\models\Contact;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use app\components\ReturnUrl;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ContactSearch $searchModel
 */

$this->title = Yii::t('app', 'Contacts');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contact-index">

    <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

</div>