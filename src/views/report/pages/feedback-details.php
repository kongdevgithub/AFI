<?php

use app\components\MenuItem;
use app\models\Contact;
use app\models\Feedback;
use app\models\Job;
use app\models\User;
use cornernote\shortcuts\Y;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Feedback Details');

$staff_rep_id = Y::GET('staff_rep_id');
$staffRep = $staff_rep_id ? User::findOne($staff_rep_id) : false;
$from = date('Y-m-d 00:00:00', strtotime(Y::GET('from')));
$to = date('Y-m-d 23:59:59', strtotime(Y::GET('to')));
$type = Y::GET('type');

?>
<div class="report-feedback-details">

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
                    ->andWhere(['between', 'feedback.created_at', strtotime($from), strtotime($to)])
                    ->andWhere(['not', ['feedback.score' => null]]);
                if ($staffRep) {
                    $feedbacks->joinWith(['jobs']);
                    $feedbacks->andWhere(['job.staff_rep_id' => $staffRep->id]);
                }
                if ($type) {
                    if ($type == 'detractors') {
                        $feedbacks->andWhere(['<=', 'feedback.score', 6]);
                    } elseif ($type == 'neutrals') {
                        $feedbacks->andWhere(['>', 'feedback.score', 6]);
                        $feedbacks->andWhere(['<=', 'feedback.score', 8]);
                    } elseif ($type == 'promoters') {
                        $feedbacks->andWhere(['>', 'feedback.score', 8]);
                    }
                }
                foreach ($feedbacks->all() as $feedback) {
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
