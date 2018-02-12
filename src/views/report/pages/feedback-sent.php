<?php

use app\components\MenuItem;
use app\models\Contact;
use app\models\Feedback;
use app\models\Job;
use cornernote\shortcuts\Y;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Feedback Sent');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();


$date = Y::GET('date');
$date = date('Y-m-d', $date ? strtotime($date) : time());
$from = date('Y-m-d 00:00:00', strtotime('first day of ' . $date));
$to = date('Y-m-d 23:59:59', strtotime('last day of ' . $date));
$paginateDateFormat = 'F Y';
$nextFormat = '1 month';

?>
<div class="report-feedback-sent">

    <div class="row">
        <div class="col-sm-3 col-md-3">
            <?= Html::a('<i class="fa fa-arrow-left"></i> ' . date($paginateDateFormat, strtotime($from . ' -' . $nextFormat)), ['/report/index', 'report' => 'feedback-sent', 'date' => date('Y-m-d', strtotime($from . ' -' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
        <div class="col-sm-6 col-md-6 text-center">
            <h2 style="margin-top: 0"><?= $this->title . ' - ' . date($paginateDateFormat, strtotime($from)); ?></h2>
            <?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?>
        </div>
        <div class="col-sm-3 col-md-3 text-right">
            <?= Html::a(date($paginateDateFormat, strtotime($from . ' +' . $nextFormat)) . ' <i class="fa fa-arrow-right"></i>', ['/report/index', 'report' => 'feedback-sent', 'date' => date('Y-m-d', strtotime($from . ' +' . $nextFormat))], ['class' => 'btn btn-default']); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-body no-padding">
            <table class="table table-condensed table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Contact') ?></th>
                    <th><?= Yii::t('app', 'Company') ?></th>
                    <th><?= Yii::t('app', 'Created') ?></th>
                    <th><?= Yii::t('app', 'Submitted') ?></th>
                    <th class="text-center"><?= Yii::t('app', 'Score') ?></th>
                    <th><?= Yii::t('app', 'Comments') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // global
                // find feedbacks
                $feedbacks = Feedback::find()
                    ->andWhere(['between', 'created_at', strtotime($from), strtotime($to)])
                    ->all();
                foreach ($feedbacks as $feedback) {
                    ?>
                    <tr>
                        <td><?= $feedback->contact->label ?></td>
                        <td><?= $feedback->contact->defaultCompany ? $feedback->contact->defaultCompany->name : '' ?></td>
                        <td><?= Yii::$app->formatter->asDatetime($feedback->created_at) ?></td>
                        <td><?= Yii::$app->formatter->asDatetime($feedback->submitted_at) ?></td>
                        <td class="text-right"><?= $feedback->score ?></td>
                        <td><?= $feedback->comments ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
