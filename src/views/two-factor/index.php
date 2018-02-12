<?php
/**
 * @var yii\web\View $this
 * @var \app\models\form\AuthySetupForm $model
 */

use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Two Factor Authentication');
?>

<div class="authy-index">

    <?php
    if (empty(Yii::$app->user->identity->two_factor['enabled'])) {
        ?>
        <div class="jumbotron">
            <h1>Status: DISABLED</h1>
            <p><?= Html::a(Yii::t('app', 'Enable Two Factor Authentication'), ['setup'], ['class' => 'btn btn-primary btn-lg']) ?></p>
        </div>
        <?php
    } else {
        ?>
        <div class="jumbotron">
            <h1>Status: ENABLED</h1>
            <p><?= Html::a(Yii::t('app', 'Disable Two Factor Authentication'), ['disable'], ['class' => 'btn btn-warning btn-lg', 'data-confirm' => Yii::t('app', 'Are you sure?')]) ?></p>
        </div>
        <?php
    }
    ?>
</div>
