<?php

use cornernote\shortcuts\Y;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Quality Check');

$action = 'job';

echo Html::beginForm(Url::to([$action]), 'get');
echo Html::tag('div', implode('', [
    Html::input('text', 'id', Y::GET('id'), ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Job ID')]),
    Html::tag('span', Html::submitInput(Yii::t('app', 'Go'), ['class' => 'btn btn-default']), ['class' => 'input-group-btn']),
]), ['class' => 'input-group']);
echo Html::endForm();

echo $this->render('_job_results', ['title' => Yii::t('app', 'Pre Fabrication'), 'status' => 'unit-fabrication/preFabrication', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Powdercoat'), 'status' => 'unit-fabrication/powdercoat', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Machining'), 'status' => 'unit-fabrication/manufacture', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Fabrication'), 'status' => 'unit-fabrication/fabrication', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Cut'), 'status' => 'unit-fabrication/cut', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Light'), 'status' => 'unit-fabrication/light', 'action' => $action]);
echo $this->render('_job_results', ['title' => Yii::t('app', 'Quality'), 'status' => 'unit-fabrication/quality', 'action' => $action]);
