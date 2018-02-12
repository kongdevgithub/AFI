<?php

use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Installer $model
 */

$this->title = Yii::t('goldoc', 'Installer') . ': ' . Yii::t('goldoc', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Installers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Create');
?>
<div class="installer-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
