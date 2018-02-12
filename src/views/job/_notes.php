<?php

use app\models\AccountTerm;
use app\models\Package;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */
?>
<?= $this->render('/note/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Notes')]) ?>
<?php if ($model->company->notes) { ?>
    <div class="box">
        <div class="box-header box-solid">
            <h3 class="box-title"><?= Yii::t('app', 'Company Notes'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($model->company->notes as $note) {
                echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
            }
            ?>
        </div>
    </div>
<?php } ?>
