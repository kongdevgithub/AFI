<?php

use app\components\MenuItem;
use app\models\Contact;
use app\models\Feedback;
use app\models\Job;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Feedback Contacts');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$contacts = Feedback::getFeedbackContacts();
$emails = [];
/** @var Contact[] $duplicates */
$duplicates = [];
foreach ($contacts as $contact_id => $jobs) {
    $contact = Contact::findOne($contact_id);
    if (isset($emails[$contact->email])) {
        $duplicates[] = $contact;
    }
}
?>
<div class="report-feedback-contacts">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Duplicates'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            if ($duplicates) {
                $items = [];
                foreach ($duplicates as $contact) {
                    $items[] = $contact->getLink($contact->getLabel()) . ' - ' . $contact->email;
                }
                echo Html::ul($items, ['encode' => false]);
            } else {
                echo Yii::t('app', 'No duplicates!');
            }
            ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $this->title . ' (' . count($contacts) . ')'; ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($contacts as $contact_id => $jobs) {
                $contact = Contact::findOne($contact_id);
                $emails[$contact->email] = $contact->email;
                echo Html::tag('h2', $contact->getLink($contact->getLabel()) . ' (' . $contact->defaultCompany->getLink($contact->defaultCompany->name) . ')');
                $items = [];
                foreach ($jobs as $job_id) {
                    $job = Job::findOne($job_id);
                    $items[] = Yii::$app->formatter->asDatetime($job->complete_at) . ' - ' . $job->getLink($job->getTitle());
                }
                echo Html::ul($items, ['encode' => false]);
            }
            ?>
        </div>
    </div>

</div>
