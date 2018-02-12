<?php

/**
 * @var yii\web\View $this
 */

use app\components\MenuItem;
use app\models\AccountTerm;
use app\models\Item;
use app\models\ItemType;
use app\models\Machine;
use app\models\search\ItemSearch;
use app\models\search\UnitSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Finance Todo');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Dashboards'), 'url' => ['dashboard/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$showColumns = [
    'name',
    'name.name',
    'name.name.company',
    'name.links',
    'name.dates',
    'report_total',
    //'status',
];

?>

<div class="dashboard-finance">

    <div class="row">

        <div class="col-sm-3">

            <?php
            // Suspended Companies
            $params = ['CompanySearch' => [
                'status' => ['company/suspended'],
            ]];
            echo $this->render('/dashboard/pages/_companies', [
                'showColumns' => ['name'],
                'title' => Html::a(Yii::t('app', 'Suspended Companies'), ['/company/index', 'CompanySearch' => $params['CompanySearch']]),
                'params' => $params,
                'orderBy' => ['company.name' => SORT_ASC],
            ]);
            ?>

            <?php
            // Redo
            ?>

            <?php
            //// PWO/COD Upcoming
            //$params = ['JobSearch' => [
            //    'status' => ['job/quote'],
            //    'invoice_sent' => 0,
            //    'account_term_id' => [AccountTerm::ACCOUNT_TERM_COD, AccountTerm::ACCOUNT_TERM_PWO]
            //]];
            //echo $this->render('_jobs', [
            //    'showColumns' => $showColumns,
            //    'headerCallback' => function ($dataProvider) {
            //        $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
            //        $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
            //        return $output;
            //    },
            //    'title' => Html::a(Yii::t('app', 'PWO/COD Upcoming'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
            //    'params' => $params,
            //]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // PWO/COD to Invoice
            $params = ['JobSearch' => [
                'status' => ['job/production', 'job/despatch', 'job/packed', 'job/complete'],
                'invoice_sent' => 0,
                'account_term_id' => [AccountTerm::ACCOUNT_TERM_COD, AccountTerm::ACCOUNT_TERM_PWO]
            ]];
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'PWO/COD to Invoice'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>

            <?php
            // Redo
            ?>

            <?php
            //// PWO/COD Upcoming
            //$params = ['JobSearch' => [
            //    'status' => ['job/quote'],
            //    'invoice_sent' => 0,
            //    'account_term_id' => [AccountTerm::ACCOUNT_TERM_COD, AccountTerm::ACCOUNT_TERM_PWO]
            //]];
            //echo $this->render('/dashboard/pages/_jobs', [
            //    'showColumns' => $showColumns,
            //    'headerCallback' => function ($dataProvider) {
            //        $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
            //        $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
            //        return $output;
            //    },
            //    'title' => Html::a(Yii::t('app', 'PWO/COD Upcoming'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
            //    'params' => $params,
            //]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // PWO/COD Unpaid
            $params = ['JobSearch' => [
                'status' => ['job/production', 'job/despatch', 'job/packed', 'job/complete'],
                'invoice_sent' => 1,
                'invoice_paid' => 0,
                'account_term_id' => [AccountTerm::ACCOUNT_TERM_COD, AccountTerm::ACCOUNT_TERM_PWO]
            ]];
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'PWO/COD Unpaid'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>

        </div>
        <div class="col-sm-3">

            <?php
            // Accounts to Invoice
            $params = ['JobSearch' => [
                'status' => 'job/complete',
                'invoice_sent' => 0,
                'account_term_id' => [AccountTerm::ACCOUNT_TERM_30DAY]
            ]];
            echo $this->render('/dashboard/pages/_jobs', [
                'showColumns' => $showColumns,
                'headerCallback' => function ($dataProvider) {
                    $output = '<span class="label label-default">' . $dataProvider->totalCount . '</span>';
                    $output .= ' $' . number_format($dataProvider->query->sum('quote_total_price-quote_freight_price-quote_tax_price'), 2);
                    return $output;
                },
                'title' => Html::a(Yii::t('app', 'Accounts to Invoice'), ['/job/index', 'JobSearch' => $params['JobSearch']]),
                'params' => $params,
            ]);
            ?>

        </div>

    </div>

</div>