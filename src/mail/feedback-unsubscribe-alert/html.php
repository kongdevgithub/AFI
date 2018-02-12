<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Contact $contact
 */

use yii\helpers\Html;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A contact has unsubscribed from feedback! <?= $contact->getLabel() ?>.</p>

