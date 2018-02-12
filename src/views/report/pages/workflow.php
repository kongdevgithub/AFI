<?php

use app\components\MenuItem;
use cornernote\workflow\manager\models\Workflow;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Workflow');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();


?>


<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('job')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('company')]) ?>
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('product')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-print')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-print')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-fabrication')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-fabrication')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-hardware')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-hardware')]) ?>
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-installation')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-installation')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-emPrint')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-emPrint')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('item-emHardware')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('unit-emHardware')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('package')]) ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('pickup')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $this->render('_workflow', ['workflow' => Workflow::findOne('goldoc-product')]) ?>
    </div>
    <div class="col-md-6">

    </div>
</div>
