<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Component $component
 * @var string $message
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A component could not be exported into dear - component_id <?= Html::a($component->id, Url::to(['component/view', 'id' => $component->id], 'https')) ?>.</p>

<p><strong>Error Message:</strong><br><?= $message; ?></p>





