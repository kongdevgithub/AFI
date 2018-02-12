<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Company;
use app\models\Job;
use cornernote\shortcuts\Y;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

//Yii::$app->controller->layout = 'box';
$this->title = Yii::t('app', 'Company Trading Time');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

?>

<div class="report-company-performance">

    <div class="row">
        <div class="col-md-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Never</h3>
                </div>
                <div class="box-body">
                    <?php
                    $companies = Company::find()
                        ->notDeleted()
                        ->andWhere(['first_job_due_date' => null])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();
                    echo Html::ul(ArrayHelper::map($companies, 'id', 'name'));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Under 1 Year</h3>
                </div>
                <div class="box-body">
                    <?php
                    $companies = Company::find()
                        ->notDeleted()
                        ->andWhere(['between', 'first_job_due_date', date('Y-m-d', strtotime('-1years')), date('Y-m-d')])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();
                    echo Html::ul(ArrayHelper::map($companies, 'id', 'name'));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">1-3 Years</h3>
                </div>
                <div class="box-body">
                    <?php
                    $companies = Company::find()
                        ->notDeleted()
                        ->andWhere(['between', 'first_job_due_date', date('Y-m-d', strtotime('-3year')), date('Y-m-d', strtotime('-1years'))])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();
                    echo Html::ul(ArrayHelper::map($companies, 'id', 'name'));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Over 3 Years</h3>
                </div>
                <div class="box-body">
                    <?php
                    $companies = Company::find()
                        ->notDeleted()
                        ->andWhere(['<=', 'first_job_due_date', date('Y-m-d', strtotime('-3years'))])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();
                    echo Html::ul(ArrayHelper::map($companies, 'id', 'name'));
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>