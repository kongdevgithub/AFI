<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\Job;
use app\models\search\JobSearch;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\DetailView;

$this->title = Yii::t('app', 'Sales Manager');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();
?>

<div class="report-sales-manager">

    <div class="row">
        <div class="col-md-4">

            <?php
            // No Staff Rep
            $params = [
                'CompanySearch' => [
                    'staff_rep_id' => Job::STAFF_LEAD_DEFAULT,
                ],
            ];
            echo $this->render('/dashboard/pages/_companies', [
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'No Staff Rep'), ['/company/index', 'CompanySearch' => $params['CompanySearch']]),
                'params' => $params,
            ]);
            ?>

        </div>
        <div class="col-md-4">

            <?php
            // NPS Detractors
            $params = [
                'FeedbackSearch' => [
                    'score' => '<7',
                    'requires_followup' => 1,
                ],
            ];
            echo $this->render('/dashboard/pages/_feedbacks', [
                'headerCallback' => function ($dataProvider) {
                    return '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                },
                'title' => Html::a(Yii::t('app', 'NPS Detractors'), ['/feedback/index', 'FeedbackSearch' => $params['FeedbackSearch']]),
                'params' => $params,
            ]);
            ?>

        </div>
    </div>

</div>