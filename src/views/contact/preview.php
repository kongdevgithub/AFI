<?php

use app\components\MenuItem;
use yii\bootstrap\Nav;
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

$this->title = $model->label;
if ($model->defaultCompany) {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['company/index', 'ru' => ReturnUrl::getRequestToken()]];
    $this->params['breadcrumbs'][] = ['label' => $model->defaultCompany->name, 'url' => ['company/view', 'id' => $model->defaultCompany->id, 'ru' => ReturnUrl::getRequestToken()]];
} else {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts'), 'url' => ['index']];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contact-preview">

    <div class="box box-widget widget-user-2">
        <div class="widget-user-header bg-blue">
            <div class="widget-user-image">
                <?= $model->getAvatar(65) ?>
            </div>
            <h3 class="widget-user-username"><?= $this->title ?></h3>
            <h5 class="widget-user-desc"><?= $model->defaultCompany ? '<i class="fa fa-briefcase"></i> ' . $model->defaultCompany->name : '-' ?></h5>
        </div>
        <div class="box-footer no-padding">
            <div class="row">
                <div class="col-md-8">
                    <ul class="nav nav-stacked">
                        <li style="padding: 20px;">
                            <ul class="list-unstyled">
                                <li>
                                    <?= Html::a('<i class="fa fa-envelope"></i> ' . $model->email, 'mailto:' . $model->email) ?>
                                </li>
                                <?php if ($model->phone): ?>
                                    <li>
                                        <?= Html::a('<i class="fa fa-phone-square"></i> ' . $model->phone, 'tel:' . preg_replace('/[^0-9]/', '', $model->phone)) ?>
                                    </li>
                                <?php endif; ?>
                                <?php if ($model->fax): ?>
                                    <li>
                                        <i class="fa fa-fax"></i> <?= $model->fax ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <!--
                        <li><a href="#">Projects <span class="pull-right badge bg-blue">31</span></a></li>
                        <li><a href="#">Tasks <span class="pull-right badge bg-aqua">5</span></a></li>
                        -->
                    </ul>
                </div>
                <div class="col-md-4">
                    <?php
                    $items = [];
                    $items[] = [
                        'label' => Yii::t('app', 'View Contact'),
                        'url' => ['//contact/view', 'id' => $model->id],
                    ];
                    if ($model->defaultCompany) {
                        $items[] = [
                            'label' => Yii::t('app', 'View Company'),
                            'url' => ['//company/view', 'id' => $model->defaultCompany->id],
                        ];
                    }
                    $items[] = [
                        'label' => Yii::t('app', 'View Jobs'),
                        'url' => ['//job/index', 'JobSearch' => ['contact_id' => $model->id]],
                    ];
                    echo Nav::widget([
                        'options' => ['class' => 'list-unstyled'],
                        'encodeLabels' => false,
                        'items' => $items,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
