<?php
/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var string $key
 */
use app\components\ReturnUrl;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Upload Artwork for Quote') . ' ' . $model->getTitle();

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="approval-quote-artwork">
    <iframe src="https://spaces.hightail.com/uplink/AFIBrandingSolutions" frameborder="0" width="100%" height="700"></iframe>
</div>

