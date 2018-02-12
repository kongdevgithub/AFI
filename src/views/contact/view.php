<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 */

$this->title = Yii::t('app', 'Contact') . ' ' . $model->label;
$this->params['heading'] = $model->label;
//if ($model->defaultCompany) {
//    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['company/index', 'ru' => ReturnUrl::getRequestToken()]];
//    $this->params['breadcrumbs'][] = ['label' => $model->defaultCompany->name, 'url' => ['company/view', 'id' => $model->defaultCompany->id, 'ru' => ReturnUrl::getRequestToken()]];
//} else {
//    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index']];
//}
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="contact-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Contact Details'); ?></h3>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'first_name',
                            'last_name',
                            'email:email',
                            'phone',
                            'fax',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?= $this->render('_companies', ['model' => $model]) ?>
        </div>
    </div>

</div>
