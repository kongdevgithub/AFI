<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 */

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/' . $job->quote_template . '/quote-approval-header.jpg';
echo $job->getQuoteEmailHtml();