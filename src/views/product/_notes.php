<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */
?>
<?php echo $this->render('/note/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Notes')]) ?>
<?php if ($model->job->notes) { ?>
    <div class="box">
        <div class="box-header box-solid">
            <h3 class="box-title"><?= Yii::t('app', 'Job Notes'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($model->job->notes as $note) {
                echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
            }
            ?>
        </div>
    </div>
<?php } ?>
<?php if ($model->job->company->notes) { ?>
    <div class="box">
        <div class="box-header box-solid">
            <h3 class="box-title"><?= Yii::t('app', 'Company Notes'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($model->job->company->notes as $note) {
                echo $this->render('/note/_view', ['model' => $note, 'showActions' => false]);
            }
            ?>
        </div>
    </div>
<?php } ?>
