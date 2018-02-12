<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */
?>
<?php echo $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Attachments')]) ?>
<?php if ($model->job->attachments) { ?>
    <div class="box">
        <div class="box-header box-solid">
            <h3 class="box-title"><?= Yii::t('app', 'Job Attachments'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($model->job->attachments as $attachment) {
                echo $this->render('/attachment/_view', ['model' => $attachment, 'showActions' => false]);
            }
            ?>
        </div>
    </div>
<?php } ?>
