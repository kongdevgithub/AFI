<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */
?>
<?php echo $this->render('/link/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Links')]) ?>
<?php if ($model->job->links) { ?>
    <div class="box">
        <div class="box-header box-solid">
            <h3 class="box-title"><?= Yii::t('app', 'Job Links'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            foreach ($model->job->links as $link) {
                echo $this->render('/link/_view', ['model' => $link, 'showActions' => false]);
            }
            ?>
        </div>
    </div>
<?php } ?>
