<?php

use app\models\AccountTerm;
use yii\bootstrap\Alert;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

// check for unpaid PWO
if ($model->account_term_id == AccountTerm::ACCOUNT_TERM_PWO) {
    if (!$model->invoice_sent) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Account Term is PWO and the Customer has not been invoiced.'),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    } elseif (!$model->invoice_paid) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Account Term is PWO and the Customer has not paid.'),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    }
}
// check for unpaid cod
if ($model->account_term_id == AccountTerm::ACCOUNT_TERM_COD) {
    if (!$model->invoice_sent) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Account Term is COD and the Customer has not been invoiced.'),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    } elseif (!$model->invoice_paid) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Account Term is COD and the Customer has not paid.'),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    }
}
