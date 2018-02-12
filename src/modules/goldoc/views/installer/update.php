<?php

use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Installer $model
 */

$this->title = Yii::t('goldoc', 'Installer') . ': ' . $model->name;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Installers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Update');
?>
<div class="installer-update">

    <?= $this->render('_menu', compact('model')); ?>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
