<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Feedback $feedback
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/feedback-survey-header.jpg';
$this->params['unsubscribeUrl'] = Url::to(['feedback/unsubscribe', 'id' => $feedback->contact_id, 'key' => md5($feedback->contact_id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))], 'https');
?>

<p>Hey there <?= $feedback->contact->first_name ?>!</p>

<p>Recently our team completed the following project<?= count($feedback->jobs) > 1 ? 's' : '' ?> for you:</p>

<ul style="list-style-type: none; padding-left: 20px;">
    <?php foreach ($feedback->jobs as $job) { ?>
        <li style="font-size: 12px;">
            <strong><?= $job->name . ' | ' . $job->company->name . ' | #' . $job->vid ?></strong>
            <?php /* ?>
            <ul>
                <?php foreach ($job->products as $product) { ?>
                    <li style="font-size: 12px;">
                        <?= $product->getDescription(['showItems' => false]) ?>
                    </li>
                <?php } ?>
            </ul>
            <?php */ ?>
        </li>
    <?php } ?>
</ul>

<p>We want you to be super happy every time you put your trust in us. Let us know how these projects went by taking a 5 second survey.</p>

<hr style="height:0;border:0;border-top: 1px solid #c2c1c1;">
<p><strong>How likely are you to recommend AFI Branding to a friend or colleague?</strong></p>

<p>
    <small style="color:#aaa">very unlikely</small> <?php
    for ($i = 1; $i <= 10; $i++) {
        $style = 'background-color: #71C0EA;border: 1px solid #00A7E0;';
        //if ($i <= 6) { // detractors
        //    $style = 'background-color: #71C0EA;border: 1px solid #00A7E0;';
        //} elseif ($i >= 9) { // promoters
        //    $style = 'background-color: #71C0EA;border: 1px solid #00A7E0;';
        //} else { // others
        //    $style = 'background-color: #71C0EA;border: 1px solid #00A7E0;';
        //}
        echo Html::a($i, Url::to([
                'feedback/thank-you',
                'id' => $feedback->id,
                'score' => $i,
                'key' => md5($feedback->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY')),
            ], 'https'), [
                'style' => 'padding: 4px 8px;color: #ffffff;font-size: 12px;text-decoration: none;' . $style,
            ]) . ' ';
    }
    ?>
    <small style="color:#aaa">very likely</small>
</p>
